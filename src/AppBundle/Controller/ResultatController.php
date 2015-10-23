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

namespace AppBundle\Controller;

use AppBundle\Domain\Election\Entity\Candidat\Specification\CandidatNuanceSpecification;
use AppBundle\Domain\Election\Entity\Echeance\Echeance;
use AppBundle\Domain\Territoire\Entity\Territoire\AbstractTerritoire;
use AppBundle\Form\EcheanceChoiceType;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResultatController extends Controller
{
    private $nuancesGroups = array(
        array('EXG', 'LEXG'),
        array('FG', 'LCOP', 'LCOM' ,'LPG', 'LFG'),
        array('VEC', 'LVEC'),
        array('SOC', 'LSOC', 'LUG'),
        array('DVG', 'LDVG', 'RDG'),
        array('AUT', 'LAUT', 'LDIV', 'REG', 'LREG', 'ECO'),
        array('CEN', 'LCMD', 'LCM', 'LMDM', 'LUC', 'LUDI', 'NCE', 'ALLI', 'PRV'),
        array('UMP', 'LMAJ', 'LUD', 'LUMP'),
        array('DVD', 'LDVD'),
        array('FN', 'LFN'),
        array('EXD', 'LEXD'),
    );

    public function resultatAction(Request $request, AbstractTerritoire $territoire)
    {
        $response = new Response();
        $response->setLastModified($this->get('repository.cache_info')->getLastModified($territoire));
        $response->setPublic();

        if ($response->isNotModified($request)) {
            return $response;
        }

        $allEcheances = $this->get('repository.echeance')->getAll();
        $form = $this->createForm(new EcheanceChoiceType($allEcheances), array(), array('method' => 'GET'));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $data = $form->getData()) {
            $reference = $data['comparaison'] ? $data['comparaison']->getNom() : null;
            $echeances = $data['echeances'];

            if (!in_array($reference, $echeances->toArray(), true)) {
                $reference = null;
            }
        } else {
            $echeances = array_filter($allEcheances, function ($element) {
                return ($element->getType() !== Echeance::MUNICIPALES);
            });
            $reference = null;
            $form->get('echeances')->setData(new ArrayCollection($echeances));
        }

        $results = $this->getResults($territoire, $echeances);

        return $this->render(
            'resultat.html.twig',
            array(
                'form' => $form->createView(),
                'resultats' => $results,
                'territoire' => $territoire->getNom(),
                'reference' => $reference,
            ),
            $response
        );
    }

    /**
     * @Route(
     *     "/circo-europeenne/{code}/{nom}",
     *     name="resultat_circo_europeenne"
     * )
     */
    public function circoEuropeenneAction(Request $request, $code, $nom)
    {
        $circo = $this
            ->get('repository.territoire')
            ->getCirconscriptionEuropeenne($code)
        ;

        if (!$circo || $this->get('cocur_slugify')->slugify($circo->getNom()) !== $nom) {
            throw $this->createNotFoundException('Circonscription inconnue.');
        }

        return $this->forward('AppBundle:Resultat:resultat', array(
            'territoire' => $circo,
        ));
    }

    /**
     * @Route(
     *     "/commune/{departement}/{code}/{nom}",
     *     name="resultat_commune"
     * )
     */
    public function communeAction(Request $request, $departement, $code, $nom)
    {
        $commune = $this
            ->get('repository.territoire')
            ->getCommune($departement, $code)
        ;

        if (!$commune || $this->get('cocur_slugify')->slugify($commune->getNom()) !== $nom) {
            throw $this->createNotFoundException('Commune inconnue.');
        }

        return $this->forward('AppBundle:Resultat:resultat', array(
            'territoire' => $commune,
        ));
    }

    /**
     * @Route(
     *     "/departement/{code}/{nom}",
     *     name="resultat_departement"
     * )
     */
    public function departementAction(Request $request, $code, $nom)
    {
        $departement = $this
            ->get('repository.territoire')
            ->getDepartement($code)
        ;

        if (!$departement || $this->get('cocur_slugify')->slugify($departement->getNom()) !== $nom) {
            throw $this->createNotFoundException('Département inconnu.');
        }

        return $this->forward('AppBundle:Resultat:resultat', array(
            'territoire' => $departement,
        ));
    }

    /**
     * @Route(
     *     "/france",
     *     name="resultat_france"
     * )
     */
    public function paysAction(Request $request)
    {
        $pays = $this
            ->get('repository.territoire')
            ->getPays()
        ;

        return $this->forward('AppBundle:Resultat:resultat', array(
            'territoire' => $pays,
        ));
    }

    /**
     * @Route(
     *     "/region/{code}/{nom}",
     *     name="resultat_region"
     * )
     */
    public function regionAction(Request $request, $code, $nom)
    {
        $region = $this
            ->get('repository.territoire')
            ->getRegion($code)
        ;

        if (!$region || $this->get('cocur_slugify')->slugify($region->getNom()) !== $nom) {
            throw $this->createNotFoundException('Région inconnue.');
        }

        return $this->forward('AppBundle:Resultat:resultat', array(
            'territoire' => $region,
        ));
    }

    private function getResults($territoire, $echeances = array())
    {
        $result = array();

        /*
         * On arrive à descendre à 4 queries par échéances à l'échelon communal.
         */
        foreach ($echeances as $echeance) {
            $result[$echeance->getNom()] = array();

            /*
             * Première query sur Election
             */
            $election = $this
                ->get('repository.election')
                ->get($echeance, $territoire)
            ;

            /*
             * Deuxième query sur VoteInfoAssignment.
             */
            $voteInfo = $this
                ->get('repository.election')
                ->getVoteInfo($echeance, $territoire)
            ;
            $result[$echeance->getNom()]['inscrits'] = $voteInfo->getInscrits();
            $result[$echeance->getNom()]['votants'] = $voteInfo->getVotants();
            $result[$echeance->getNom()]['exprimes'] = $voteInfo->getExprimes();
            $result[$echeance->getNom()]['election'] = $election;

            /*
             * Idéalement, le repository est assez optimisé pour qu'il n'y ait
             * des requetes que lors de la première boucle.
             */
            foreach ($this->nuancesGroups as $nuances) {
                $spec = new CandidatNuanceSpecification($nuances);
                /*
                 * À l'échelon communal, on arrive à descendre à une seule
                 * requete ici. (C'est donc la troisième, sur ScoreAssignment).
                 */
                $score = $this
                    ->get('repository.election')
                    ->getScore(
                        $echeance,
                        $territoire,
                        $spec
                    )
                ;

                /*
                 * Si il y a une élection à l'échelle de ce territoire, on
                 * récupère les noms des candidats associés à la nuance.
                 */
                $candidats = array();
                $sieges = 0;
                if ($election) {
                    /*
                     * Quatrième query (si il n'y en a eu qu'une pour les
                     * résultats), sur la table Candidat.
                     */
                    $candidats = array_filter(
                        $election->getCandidats(),
                        array($spec, 'isSatisfiedBy')
                    );

                    if ($election->getCirconscription() === $territoire) {
                        foreach ($candidats as $candidat) {
                            $sieges += $election->getSiegesCandidat($candidat);
                        }
                    }
                }

                $result[$echeance->getNom()][$nuances[0]] = array();
                $result[$echeance->getNom()][$nuances[0]]['score'] = $score;
                $result[$echeance->getNom()][$nuances[0]]['sieges'] = $sieges;
                $result[$echeance->getNom()][$nuances[0]]['candidats'] =
                    $candidats;
            }
        }

        return $result;
    }

    private function fakeCompletedResult($echeance, $territoire, $election)
    {
        foreach ($election->getCandidats() as $candidat) {
            $voix = $this
                ->get('repository.election')
                ->getScore(
                    $echeance,
                    $territoire,
                    $candidat
                )->toVoix()
            ;
            $election->setVoixCandidat(
                $voix,
                $candidat
            );
        }
    }
}
