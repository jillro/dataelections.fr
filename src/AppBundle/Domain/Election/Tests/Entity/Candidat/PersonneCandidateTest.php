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
use AppBundle\Domain\Election\Entity\Candidat\PersonneCandidate;
use AppBundle\Domain\Election\Entity\Echeance\Echeance;
use AppBundle\Domain\Election\Tests\Entity\Election\ElectionMock;
use AppBundle\Domain\Territoire\Tests\Entity\Territoire\TerritoireMock;

class PersonneCandidateTest extends \PHPUnit_Framework_TestCase
{
    public function testHasNomAndPrenomAndElection()
    {
        $echeance = new Echeance(new \DateTime(), Echeance::CANTONALES);
        $circonscription = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);
        $personneCandidate = new PersonneCandidate(
            $election,
            'FG',
            'Naël',
            'Ferret'
        );

        $this->assertEquals('FG', $personneCandidate->getNuance());
        $this->assertEquals('Naël Ferret', (string) $personneCandidate);
        $this->assertEquals($election, $personneCandidate->getElection());
    }

    public function testIsCandidat()
    {
        $echeance = new Echeance(new \DateTime(), Echeance::CANTONALES);
        $circonscription = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);
        $personneCandidate = new PersonneCandidate(
            $election,
            'FG',
            'Naël',
            'Ferret'
        );

        $this->assertTrue($personneCandidate instanceof CandidatInterface);
    }

    public function testNomIsString()
    {
        $echeance = new Echeance(new \DateTime(), Echeance::CANTONALES);
        $circonscription = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);
        $this->setExpectedException(
            '\InvalidArgumentException'
        );

        $personneCandidate = new PersonneCandidate($election, 'FG', 'Naël', 42);
    }

    public function testPrenomIsString()
    {
        $echeance = new Echeance(new \DateTime(), Echeance::CANTONALES);
        $circonscription = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);
        $this->setExpectedException(
            '\InvalidArgumentException'
        );

        $personneCandidate = new PersonneCandidate(
            $election,
            'FG',
            42,
            'Ferret'
        );
    }
}
