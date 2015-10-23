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

namespace AppBundle\Domain\Territoire\Entity\Territoire;

use Doctrine\Common\Collections\ArrayCollection;

class Commune extends AbstractTerritoire
{
    /**
     * Les arrondissements de la commune.
     *
     * @var ArrayCollection
     */
    private $arrondissements;

    /**
     * Le code INSEE de la commune.
     *
     * @var int
     */
    private $code;

    /**
     * Le département de la commune.
     *
     * @var Departement
     */
    private $departement;

    /**
     * Constructeur d'objet Commune.
     *
     * @param Departement $departement Le département de la commune.
     * @param int         $code        Le code INSEE de la commune.
     * @param string      $nom         Le nom de la commune.
     */
    public function __construct(Departement $departement, $code, $nom)
    {
        \Assert\that((string) $code)->maxLength(10);
        \Assert\that($nom)
            ->string()
            ->maxLength(
                255,
                'Le nom de la commune ne peut dépasser 255 caractères.'
            )
        ;

        $this->arrondissements = new ArrayCollection();
        $this->departement = $departement;
        $departement->addCommune($this);
        $this->code = $code;
        $this->nom = $nom;
    }

    /**
     * @internal
     *
     * @param ArrondissementCommunal $arrondissement L'arrondissement.
     */
    public function addArrondissement(ArrondissementCommunal $arrondissement)
    {
        if (!$this->arrondissements->contains($arrondissement)) {
            $this->arrondissements[] = $arrondissement;
        }
    }

    /**
     * Récupérer les éventuels arrondissements de la communes.
     *
     * @return ArrondissementCommunal Les arrondissements.
     */
    public function getArrondissements()
    {
        return $this->arrondissements;
    }

    /**
     * Récupérer le code INSEE de la commune.
     *
     * @return int Le code INSEE de la commune.
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Récupérer le département de la commune.
     *
     * @return Departement Le département de la commune.
     */
    public function getDepartement()
    {
        return $this->departement;
    }
}
