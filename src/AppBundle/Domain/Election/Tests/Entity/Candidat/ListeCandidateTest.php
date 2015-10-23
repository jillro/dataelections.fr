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

namespace AppBundle\Domain\Election\Tests\Entity\Candidat;

use AppBundle\Domain\Election\CandidatInterface;
use AppBundle\Domain\Election\Entity\Candidat\ListeCandidate;
use AppBundle\Domain\Election\Entity\Echeance\Echeance;
use AppBundle\Domain\Election\Tests\Entity\Election\ElectionMock;
use AppBundle\Domain\Territoire\Tests\Entity\Territoire\TerritoireMock;

class ListeCandidateTest extends \PHPUnit_Framework_TestCase
{
    public function testHasNomAndElection()
    {
        $echeance = new Echeance(new \DateTime(), Echeance::CANTONALES);
        $circonscription = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);
        $listeCandidate = new ListeCandidate($election, 'FG', 'Liste FdG');

        $this->assertEquals('FG', $listeCandidate->getNuance());
        $this->assertEquals('Liste FdG', (string) $listeCandidate);
        $this->assertEquals($election, $listeCandidate->getElection());
    }

    public function testIsCandidat()
    {
        $echeance = new Echeance(new \DateTime(), Echeance::CANTONALES);
        $circonscription = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);
        $listeCandidate = new ListeCandidate($election, 'FG', 'Liste FdG');

        $this->assertTrue($listeCandidate instanceof CandidatInterface);
    }

    public function testNomIsString()
    {
        $this->setExpectedException(
            '\InvalidArgumentException'
        );

        $echeance = new Echeance(new \DateTime(), Echeance::CANTONALES);
        $circonscription = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);
        $listeCandidate = new ListeCandidate($election, 'FG', 12);
    }
}
