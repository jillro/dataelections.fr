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

namespace AppBundle\Domain\Election\Entity\Candidat;

use AppBundle\Domain\Election\CandidatInterface;

abstract class Candidat implements CandidatInterface
{
    private $id;

    /**
     * L'élection à laquelle le candidat participait.
     *
     * @var Election
     */
    protected $election;

    /**
     * La nuance ministérielle du candidat.
     *
     * @var string
     */
    protected $nuance;

    /**
     * Récupérer l'élection à laquelle participait la liste.
     *
     * @return Election L'élection à laquelle participait la liste.
     */
    public function getElection()
    {
        return $this->election;
    }

    /**
     * Récupérer la nuance ministèrielle.
     *
     * @return string
     */
    public function getNuance()
    {
        return $this->nuance;
    }

    /**
     * Changer la nuance du candidat.
     *
     * @param string $nuance La nuance du candidiat.
     */
    public function setNuance($nuance)
    {
        $this->nuance = $nuance;
    }
}
