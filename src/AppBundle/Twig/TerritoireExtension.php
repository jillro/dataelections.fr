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

namespace AppBundle\Twig;

use AppBundle\Domain\Territoire\Entity\Territoire\AbstractTerritoire;
use Doctrine\Common\Util\ClassUtils;

class TerritoireExtension extends \Twig_Extension
{
    private $router;

    private $slugify;

    public function __construct($router, $slugify)
    {
        $this->router = $router;
        $this->slugify = $slugify;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('display_territoire', array($this, 'displayTerritoire')),
            new \Twig_SimpleFilter('resultats_link_territoire', array($this, 'resultatsLinkTerritoire')),
        );
    }

    public function displayTerritoire($territoire, $separator = ' - ', $reverse = false)
    {
        if (!$territoire instanceof AbstractTerritoire) {
            return '';
        }

        switch (ClassUtils::getRealClass(get_class($territoire))) {
            case 'AppBundle\Domain\Territoire\Entity\Territoire\CirconscriptionEuropeenne':
                return 'Circonscription européenne'.$separator.$territoire->getNom();
            case 'AppBundle\Domain\Territoire\Entity\Territoire\Departement':
                return $territoire->getNom().' (Département '.$territoire->getCode().')';
            case 'AppBundle\Domain\Territoire\Entity\Territoire\Region':
                return $territoire->getNom().' (Region)';
            case 'AppBundle\Domain\Territoire\Entity\Territoire\Commune':
                return $territoire->getNom().' ('.$territoire->getDepartement().')';
        }

        return $territoire->getNom();
    }

    public function resultatsLinkTerritoire($territoire)
    {
        switch (ClassUtils::getRealClass(get_class($territoire))) {
            case 'AppBundle\Domain\Territoire\Entity\Territoire\CirconscriptionEuropeenne':
                $route = 'resultat_circo_europeenne';
                $parameters = array(
                    'code' => $territoire->getCode(),
                    'nom' => $this->slugify->slugify($territoire->getNom()),
                );
                break;
            case 'AppBundle\Domain\Territoire\Entity\Territoire\Commune':
                $route = 'resultat_commune';
                $parameters = array(
                    'departement' => $territoire->getDepartement()->getCode(),
                    'code' => $territoire->getCode(),
                    'nom' => $this->slugify->slugify($territoire->getNom()),
                );
                break;
            case 'AppBundle\Domain\Territoire\Entity\Territoire\Departement':
                $route = 'resultat_departement';
                $parameters = array(
                    'code' => $territoire->getCode(),
                    'nom' => $this->slugify->slugify($territoire->getNom()),
                );
                break;
            case 'AppBundle\Domain\Territoire\Entity\Territoire\Pays':
                $route = 'resultat_france';
                $parameters = array();
                break;
            case 'AppBundle\Domain\Territoire\Entity\Territoire\Region':
                $route = 'resultat_region';
                $parameters = array(
                    'code' => $territoire->getCode(),
                    'nom' => $this->slugify->slugify($territoire->getNom()),
                );
                break;
        }

        if (isset($route, $parameters)) {
            return $this
                ->router
                ->generate($route, $parameters);
        }

        return '';
    }

    public function getName()
    {
        return 'territoire_extension';
    }
}
