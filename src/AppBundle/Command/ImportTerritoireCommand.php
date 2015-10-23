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

use AppBundle\Domain\Territoire\Entity\Territoire\ArrondissementCommunal;
use AppBundle\Domain\Territoire\Entity\Territoire\CirconscriptionEuropeenne;
use AppBundle\Domain\Territoire\Entity\Territoire\CirconscriptionLegislative;
use AppBundle\Domain\Territoire\Entity\Territoire\Commune;
use AppBundle\Domain\Territoire\Entity\Territoire\Departement;
use AppBundle\Domain\Territoire\Entity\Territoire\Region;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportTerritoireCommand extends AbstractImportCommand
{
    protected function configure()
    {
        $this
            ->setName('elections:import:territoire')
            ->setDescription('Importer les territoires dans la BDD.')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'Chemin du fichier à importer.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input = $input;

        $fileHandle = $this->getFileHandle();
        $type = $this->askImportType();

        $progress = $this->getHelperSet()->get('progress');
        $repo = $this->getContainer()->get('repository.territoire');

        $progress->start($output, $this->countLines($fileHandle));

        for ($i = 1; !feof($fileHandle); $i++) {
            $line = fgets($fileHandle);

            try {
                $territoire = $this->createTerritoire($line, $type);
            } catch (\InvalidArgumentException $exception) {
                $output->writeln(
                    'Erreur d\'importation ligne '.$i.': '
                    .$exception->getMessage()
                );
                continue;
            }

            $repo->add($territoire);
            $progress->advance();
            if ($i % 20 === 0) {
                $repo->save();
            }
        }

        fclose($fileHandle);
        $repo->save();
        $progress->finish();

        $output->writeln('Terminé !');
    }

    private function askImportType()
    {
        $dialog = $this->getHelperSet()->get('dialog');

        return $dialog->askAndValidate(
            $this->output,
            "<question>Quel type de territoire importer ?</question>\n".
            '(ArrondissementCommunal, Commune, Departement, Region,'.
            ' CirconscriptionLegislative, CirconscriptionEuropeenne) : ',
            function ($type) {
                if (!in_array($type, array(
                    'ArrondissementCommunal',
                    'CirconscriptionEuropeenne',
                    'CirconscriptionLegislative',
                    'Commune',
                    'Departement',
                    'Region',
                ), true)) {
                    throw new \RunTimeException(
                        'Ce n\'est pas un type existant'
                    );
                }

                return $type;
            }
        );
    }

    private function createArrondissementCommunal($properties)
    {
        $repo = $this->getContainer()->get('repository.territoire');
        $commune = $repo->getCommune($properties[0], $properties[1]);

        if (!($commune instanceof Commune) || !isset($properties[3])) {
            throw new \InvalidArgumentException('Ligne probablement vide.');
        }

        return new ArrondissementCommunal(
            $commune,
            $properties[2],
            $properties[3]
        );
    }

    private function createCirconscriptionLegislative($properties)
    {
        $repo = $this->getContainer()->get('repository.territoire');
        $departement = $repo->getDepartement($properties[0]);

        if ($departement) {
            return new CirconscriptionLegislative($departement, $properties[1]);
        }

        throw new \InvalidArgumentException(
            'Pas de département avec le code '.$properties[0]
        );
    }

    private function createCirconscriptionEuropeenne($properties)
    {
        return new CirconscriptionEuropeenne($pays, $properties[0], $properties[1]);
    }

    private function createCommune($properties)
    {
        $repo = $this->getContainer()->get('repository.territoire');
        $departement = $repo->getDepartement($properties[1]);

        $properties[3] = trim($properties[3], '()')
            .(strpos($properties[3], '\'') ? '' : ' ')
            .$properties[4]
        ;

        return new Commune($departement, $properties[2], $properties[3]);
    }

    private function createDepartement($properties)
    {
        $repo = $this->getContainer()->get('repository.territoire');
        $region = $repo->getRegion($properties[0]);

        return new Departement($region, $properties[1], $properties[2]);
    }

    private function createRegion($properties)
    {
        return new Region($properties[0], $properties[1]);
    }

    private function createTerritoire($line, $type)
    {
        $line = explode(';', trim($line));
        switch ($type) {
            case 'Region':
                return $this->createRegion($line);
            case 'Departement':
                return $this->createDepartement($line);
            case 'Commune':
                if (!in_array($line[0], array(1, 2, 3), true)) {
                    continue 2;
                }

                return $this->createCommune($line);
            case 'ArrondissementCommunal':
                return $this->createArrondissementCommunal($line);
            case 'CirconscriptionLegislative':
                return $this->createCirconscriptionLegislative($line);
            case 'CirconscriptionEuropeenne':
                return $this->createCirconscriptionEuropeenne($line);
        }
    }
}
