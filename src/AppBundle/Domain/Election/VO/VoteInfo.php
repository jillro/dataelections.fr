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

namespace AppBundle\Domain\Election\VO;

class VoteInfo
{
    /**
     * Le nombre d'exprimes.
     *
     * @var int
     */
    private $exprimes;

    /**
     * Le nombre d'inscrits.
     *
     * @var int
     */
    private $inscrits;

    /**
     * Le nombre de votants.
     *
     * @var int
     */
    private $votants;

    /**
     * Instancie un objet VoteInfo.
     *
     * @param int $inscrits Le nombre d'inscrits.
     * @param int $votants  Le nombre de votants.
     * @param int $exprimes Le nombre d'exprimes.
     */
    public function __construct($inscrits, $votants, $exprimes)
    {
        \Assert\that((integer) $inscrits)->nullOr()
            ->integer()
            ->min($votants)
            ->min($exprimes)
        ;

        \Assert\that((integer) $votants)->nullOr()
            ->integer()
            ->max($inscrits)
            ->min($exprimes)
        ;

        \Assert\that((integer) $exprimes)->nullOr()
            ->integer()
            ->max($votants)
            ->max($inscrits)
        ;

        $this->inscrits = $inscrits;
        $this->votants = $votants;
        $this->exprimes = $exprimes;
    }

    /**
     * Récupérer le nombre d'exprimes.
     *
     * @return int Le nombre d'exprimes.
     */
    public function getExprimes()
    {
        return $this->exprimes;
    }

    /**
     * Récupérer le nombre d'inscrits.
     *
     * @return int Le nombre d'inscrits.
     */
    public function getInscrits()
    {
        return $this->inscrits;
    }

    /**
     * Récupérer le nombre de votants.
     *
     * @return int Le nombre de votants.
     */
    public function getVotants()
    {
        return $this->votants;
    }
}
