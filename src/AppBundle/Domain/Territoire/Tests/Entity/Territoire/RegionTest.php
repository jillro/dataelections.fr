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

namespace AppBundle\Domain\Territoire\Tests\Entity\Territoire;

use AppBundle\Domain\Territoire\Entity\Territoire\AbstractTerritoire;
use AppBundle\Domain\Territoire\Entity\Territoire\CirconscriptionEuropeenne;
use AppBundle\Domain\Territoire\Entity\Territoire\Pays;
use AppBundle\Domain\Territoire\Entity\Territoire\Region;

class RegionTest extends \PHPUnit_Framework_TestCase
{
    public function testCirconscriptionEuropeenne()
    {
        $pays = new Pays('France');
        $region = new Region($pays, 11, 'Île-de-France');
        $circo = new CirconscriptionEuropeenne($pays, 1, 'Île-de-France');

        $region->setCirconscriptionEuropeenne($circo);

        $this->assertEquals($circo, $region->getCirconscriptionEuropeenne());
        $this->assertContains($region, $circo->getRegions());
    }

    public function testPays()
    {
        $pays = new Pays('France');
        $region = new Region($pays, 11, 'Île-de-France');

        $this->assertEquals($pays, $region->getPays());
        $this->assertContains($region, $pays->getRegions());
    }

    public function testCodeIsStringMax4()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $pays = new Pays('France');
        $region = new Region($pays, 'ZEEEE', 'Île-de-France');
    }

    public function testHasCodeAndNomAndPays()
    {
        $pays = new Pays('France');
        $region = new Region($pays, 11, 'Île-de-France');

        $this->assertEquals(11, $region->getCode());
        $this->assertEquals('Île-de-France', $region->getNom());
        $this->assertEquals($pays, $region->getPays());
    }

    public function testIsTerritoire()
    {
        $pays = new Pays('France');
        $region = new Region($pays, 11, 'Île-de-France');

        $this->assertTrue($region instanceof AbstractTerritoire);
    }

    public function testNomIsStringMax255()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $pays = new Pays('France');
        $region = new Region(
            $pays,
            'ZEEE',
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'
            .'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'
            .'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'
            .'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'
            .'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'
            .'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'
            .'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'
            .'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'
        );
    }
}
