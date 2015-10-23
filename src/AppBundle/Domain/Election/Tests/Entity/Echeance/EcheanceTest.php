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

namespace AppBundle\Domain\Election\Tests\Entity\Echeance;

use AppBundle\Domain\Election\Entity\Echeance\Echeance;

class EcheanceTest extends \PHPUnit_Framework_TestCase
{
    public function testHasDateAndTypeAndTour()
    {
        $date = new \DateTime();
        $echeance = new Echeance($date, Echeance::CANTONALES, true);

        $this->assertEquals($date, $echeance->getDate());
        $this->assertEquals(
            'Cantonales '.$date->format('Y').' (second tour)',
            $echeance->getNom()
        );
        $this->assertEquals(
            'Cantonales '.$date->format('Y').' (second tour)',
            $echeance->__toString()
        );
        $this->assertTrue($echeance->isSecondTour());
    }

    public function testNomIsString()
    {
        $this->setExpectedException(
            '\InvalidArgumentException'
        );

        $date = new \DateTime();
        $echeance = new Echeance($date, 12);
    }
}
