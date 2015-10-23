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
use AppBundle\Domain\Territoire\Entity\Territoire\Commune;
use AppBundle\Domain\Territoire\Entity\Territoire\Departement;
use AppBundle\Domain\Territoire\Entity\Territoire\Pays;
use AppBundle\Domain\Territoire\Entity\Territoire\Region;

class CommuneTest extends \PHPUnit_Framework_TestCase
{
    public function testCodeIsNumeric()
    {
        $pays = new Pays('France');
        $region = new Region($pays, 82, 'Rhône-Alpes');
        $departement = new Departement($region, 38, 'Isère');
        $commune = new Commune($departement, 'ZE', 'Grenoble');
    }

    public function testHasDepartementAndCodeAndNom()
    {
        $pays = new Pays('France');
        $region = new Region($pays, 82, 'Rhône-Alpes');
        $departement = new Departement($region, 38, 'Isère');
        $commune = new Commune($departement, 185, 'Grenoble');

        $this->assertEquals('Grenoble', $commune->getNom());
        $this->assertEquals(185, $commune->getCode());
        $this->assertEquals(
            $departement,
            $commune->getDepartement()
        );
        $this->assertContains($commune, $departement->getCommunes());
    }

    public function testIsTerritoire()
    {
        $pays = new Pays('France');
        $region = new Region($pays, 82, 'Rhône-Alpes');
        $departement = new Departement($region, 38, 'Isère');
        $commune = new Commune($departement, 185, 'Grenoble');

        $this->assertTrue($commune instanceof AbstractTerritoire);
    }

    public function testNomIsStringMax255()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $pays = new Pays('France');
        $region = new Region($pays, 82, 'Rhône-Alpes');
        $departement = new Departement($region, 38, 'Isère');
        $commune = new Commune(
            $departement,
            185,
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
