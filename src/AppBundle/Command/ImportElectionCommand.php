<?php

/*
 * Copyright 2015 Guillaume Royer
 *
 * This file is part of DataElections.
 *
 * DataElections is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * DataElections is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with DataElections. If not, see <http://www.gnu.org/licenses/>.
 */

namespace AppBundle\Command;

use AppBundle\Domain\Election\Entity\Candidat\Candidat;
use AppBundle\Domain\Election\Entity\Candidat\ListeCandidate;
use AppBundle\Domain\Election\Entity\Candidat\PersonneCandidate;
use AppBundle\Domain\Election\Entity\Echeance\Echeance;
use AppBundle\Domain\Election\VO\VoteInfo;
use AppBundle\Domain\Territoire\Entity\Territoire\Commune;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportElectionCommand extends AbstractImportCommand
{
    private $prevCommune;

    private $nuanceAvant = false;

    protected function configure()
    {
        $this
            ->setName('elections:import:election')
            ->setDescription(
                'Importer des données sur des élections dans la'.
                ' base de données.'
            )
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'Chemin du fichier à importer.'
            )
            ->addOption(
                'no-cache-invalidate',
                null,
                InputOption::VALUE_NONE,
                'Si défini, le cache des résultats n\'est pas invalidé'
            )
            ->addOption(
                'nuance-before-nom',
                null,
                InputOption::VALUE_NONE,
                'Si la nuance est ans la colonne précédent le nom du candidat ,'
                .' et non l\'inverse, cette option doit être activée.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        if ($input->getOption('no-cache-invalidate')) {
            $this->getContainer()->get('listener.cache_info.doctrine')->switchOff(true);
        }
        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        if ($input->getOption('nuance-before-nom')) {
            $this->nuanceAvant = true;
        }

        $fileHandle = $this->getFileHandle();
        $echeance = $this->askEcheanceAndCreate();
        $this->electionType = $this->askListeOuPersonne();

        $this->output->writeln(
            'Quelle est le type de circonscription de cette '.
            'élection ?'
        );
        $circoType = $this->askTerritoire();
        $output->writeln('Quel est le niveau de granularité des résultats ? ');
        $niveauResultats = $this->askTerritoire();

        $progress = $this->getHelperSet()->get('progress');
        $repo = $this->getContainer()->get('repository.election');

        $progress->start($output, $this->countLines($fileHandle));

        for ($i = 1; !feof($fileHandle); $i++) {
            $line = fgets($fileHandle);

            try {
                $this->createOrUpdateElection($line, $circoType, $echeance);
            } catch (\InvalidArgumentException $exception) {
                $output->writeln('');
                $output->writeln(
                    'Erreur d\'importation ligne '.$i.': '
                    .$exception->getMessage()
                );
                continue;
            }

            $progress->advance();
            if ($i % 20 === 0) {
                $repo->save();
                $em = $this->getContainer()->get('doctrine')->getManager();
                $em->clear();
                $echeance = $this
                    ->getContainer()
                    ->get('repository.echeance')
                    ->get($echeance->getDate(), $echeance->getType());
            }
        }

        fclose($fileHandle);
        $repo->save();
        $progress->finish();

        $output->writeln('Terminé !');
    }

    private function askEcheanceAndCreate()
    {
        $output = $this->output;
        $dialog = $this->getHelperSet()->get('dialog');

        $repo = $this->getContainer()->get('repository.echeance');

        $date = $dialog->askAndValidate(
            $output,
            'Date de l\'échéance électorale (MM/JJ/AAAA) : ',
            function ($date) {
                $date = date_create($date);
                if (!$date) {
                    throw new \RunTimeException(
                        'Ce n\'est pas une date valide.'
                    );
                }

                return $date;
            },
            false,
            '06/20/2012'
        );

        $output->writeln(Echeance::MUNICIPALES.' Municipales');
        $output->writeln(Echeance::CANTONALES.' Cantonales');
        $output->writeln(Echeance::REGIONALES.' Régionales');
        $output->writeln(Echeance::LEGISLATIVES.' Législatives');
        $output->writeln(Echeance::PRESIDENTIELLE.' Présidentielle');
        $output->writeln(Echeance::EUROPEENNES.' Européenne');
        $type = $dialog->askAndValidate(
            $output,
            'Type de l\'échéance électorale : ',
            function ($type) {
                if (!in_array($type, array(
                    Echeance::MUNICIPALES,
                    Echeance::CANTONALES,
                    Echeance::REGIONALES,
                    Echeance::LEGISLATIVES,
                    Echeance::PRESIDENTIELLE,
                    Echeance::EUROPEENNES,
                ), true)) {
                    throw new \RunTimeException(
                        'Ce n\'est pas un type valide.'
                    );
                }

                return $type;
            },
            false
        );
        $secondTour = $dialog->askConfirmation(
            $output,
            '<question>Est-ce un second tour ?</question> ',
            false
        );

        $echeance = $repo->get($date, $type);

        if (!$echeance) {
            $echeance = new Echeance($date, $type, $secondTour);
        }

        $repo->add($echeance);
        $repo->save();
        $echeance = $repo->get($date, $type);

        return $echeance;
    }

    private function askListeOuPersonne()
    {
        $dialog = $this->getHelperSet()->get('dialog');

        return $dialog->askAndValidate(
            $this->output,
            '<question>Election de liste ou uninominale ?</question> liste,'
            .' uninominale : ',
            function ($type) {
                if (!in_array($type, array(
                    'liste',
                    'uninominale',
                ), true)) {
                    throw new \RunTimeException(
                        'Vous devez choisir "liste" ou "uninominale"'
                    );
                }

                return $type;
            }
        );
    }

    private function askTerritoire()
    {
        $dialog = $this->getHelperSet()->get('dialog');

        return $dialog->askAndValidate(
            $this->output,
            "<question>Quel type de territoire ?</question>\n".
            '(ArrondissementCommunal, Commune, Departement, Region,'.
            ' CirconscriptionLegislative, CirconscriptionEuropeenne, Pays) : ',
            function ($type) {
                if (!in_array($type, array(
                    'ArrondissementCommunal',
                    'CirconscriptionEuropeenne',
                    'CirconscriptionLegislative',
                    'Commune',
                    'Departement',
                    'Region',
                    'Pays',
                ), true)) {
                    throw new \RunTimeException(
                        'Ce n\'est pas un type existant'
                    );
                }

                return $type;
            }
        );
    }

    private function createOrUpdateElection($line, $circoType, $echeance)
    {
        $repoElection = $this->getContainer()->get('repository.election');
        $repoTerritoire = $this->getContainer()->get('repository.territoire');

        // On enlève tous les espaces inutiles plus les dernières colonnes vides.
        $line = trim($line);
        $line = trim($line, ';');
        // On découpe par colonnes vides
        $lineGroups = explode(';;', $line);
        // On extrait les infos de la commune
        $communeInfos = explode(';', $lineGroups[0]);
        // On envoie une erreur si y a pas tout
        for ($i = 0; $i < 6; $i++) {
            if (!array_key_exists($i, $communeInfos)) {
                throw new \InvalidArgumentException('Circonscription inconnues.');
            }
        }

        $departement = $communeInfos[0];
        $code = $communeInfos[1];
        $commune = $repoTerritoire->getCommune($departement, $code);
        if (!$commune) {
            if (!array_key_exists($i, $communeInfos)) {
                throw new \InvalidArgumentException(
                    'Commune département '.$departement.' code '
                    .$code.'.'
                );
            }
        }
        $circonscription =
            $this->getCirconscriptionFromCommune($commune, $circoType);
        $election = $repoElection->get($echeance, $circonscription);

        if (!$election) {
            if ('liste' === $this->electionType) {
                $electionclass = 'AppBundle\Domain\Election\Entity\Election'
                .'\ElectionDeListe';
            } elseif ('uninominale' === $this->electionType) {
                $electionclass =
                    'AppBundle\Domain\Election\Entity\Election'
                    .'\ElectionUninominale';
            }
            $election = new $electionclass($echeance, $circonscription);
            $repoElection->add($election);
            $repoElection->save();
        }

        $inscrits = $communeInfos[3];
        $votants = $communeInfos[4];
        $exprimes = $communeInfos[5];

        if ($this->prevCommune
            && ($this->prevCommune->getNom() === $commune->getNom())
        ) {
            $prevVoteInfo = $election->getVoteInfo($commune);
            $inscrits += $prevVoteInfo->getInscrits();
            $votants += $prevVoteInfo->getVotants();
            $exprimes += $prevVoteInfo->getExprimes();
        }

        $election->setVoteInfo(
            new VoteInfo($inscrits, $votants, $exprimes),
            $commune
        );

        foreach ($lineGroups as $key => $lineGroup) {
            if (0 === $key) {
                continue;
            }
            $this->updateCandidat($lineGroup, $election, $commune);
        }

        $this->prevCommune = $commune;

        return $election;
    }

    private function getCirconscriptionFromCommune($commune, $type)
    {
        switch ($type) {
            case 'Region':
                return $commune->getDepartement()->getRegion();
            case 'Departement':
                return $commune->getDepartement();
            case 'Commune':
                return $commune;
            case 'CirconscriptionEuropeenne':
                return $commune->getDepartement()->getRegion()
                    ->getCirconscriptionEuropeenne();
            case 'Pays':
                return $this
                    ->getContainer()
                    ->get('repository.territoire')
                    ->getPays()
                ;
        }
    }

    /**
     * Lit une partie de CSV concernant un candidat.
     *
     * @param string $line La partie de ligne CSV
     *
     * @return Candidat L'entite Candidat
     */
    private function updateCandidat($line, $election, $commune)
    {
        $candidatInfos = explode(';', $line);
        for ($i = 0; $i < 3; $i++) {
            if (!array_key_exists($i, $candidatInfos)) {
                return false;
            }
        }

        $nuance = $this->nuanceAvant ? $candidatInfos[0] : $candidatInfos[1];
        $nom = $this->nuanceAvant ? $candidatInfos[1] : $candidatInfos[0];

        if ('liste' === $this->electionType) {
            $candidat = new ListeCandidate(
                $election,
                $nuance,
                $nom
            );
        } elseif ('uninominale' === $this->electionType) {
            $candidat = new PersonneCandidate(
                $election,
                $nuance,
                null,
                $nom
            );
        }

        $slice = array_values(
            array_filter(
                $election->getCandidats(),
                function ($c) use ($candidat) {
                    return ($candidat->getNom() === $c->getNom());
                }
            )
        );

        if (0 === count($slice)) {
            $election->addCandidat($candidat);
        } else {
            $candidat = $slice[0];
        }

        $election->setVoixCandidat($candidatInfos[2], $candidat, $commune);
    }
}
