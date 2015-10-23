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

abstract class AbstractTerritoire
{
    private $id;

    protected $nom;

    public function __construct($nom = null)
    {
        $this->nom = $nom;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Récupérer le nom du territoire.
     *
     * @return string Le nom du territoire.
     */
    public function getNom()
    {
        return $this->nom;
    }

    public function __toString()
    {
        return $this->getNom() ? $this->getNom() : '';
    }
}
