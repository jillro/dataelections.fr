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

namespace AppBundle\Domain\Election\Entity\Election;

use AppBundle\Domain\Election\VO\VoteInfo;
use AppBundle\Domain\Territoire\Entity\Territoire\AbstractTerritoire;

class VoteInfoAssignment
{
    private $id;
    private $election;
    private $territoire;
    private $territoire_id;
    private $voteInfoVO;

    public function __construct(
        Election $election,
        AbstractTerritoire $territoire,
        VoteInfo $voteInfo = null
    ) {
        $this->voteInfoVO = $voteInfo;
        $this->election = $election;
        $this->territoire = $territoire;
    }

    public function getElection()
    {
        return $this->election;
    }

    public function getTerritoire()
    {
        return $this->territoire;
    }

    public function getTerritoire_id()
    {
        return $this->territoire_id;
    }

    public function getVoteInfoVO()
    {
        return $this->voteInfoVO;
    }

    public function setVoteInfoVO(VoteInfo $voteInfo)
    {
        $this->voteInfoVO = $voteInfo;
    }
}
