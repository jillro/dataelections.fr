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

namespace AppBundle\Tests\Repository\CacheInfo;

use AppBundle\Domain\Territoire\Entity\Territoire\Pays;
use AppBundle\Domain\Territoire\Entity\Territoire\Region;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DoctrineCacheInfoRepositoryTest extends WebTestCase
{
    public function setUp()
    {
        $c = $this->container->get('doctrine.dbal.default_connection');
        $c->transactional(function ($c) {
            $sm = $c->getSchemaManager();
            $tables = $sm->listTables();

            foreach ($tables as $table) {
                $c->query('DELETE FROM '.$table->getName());
            }
        });
    }

    public function tearDown()
    {
        $this->setUp();
    }

    public function __construct()
    {
        $client = static::createClient();

        $this->container = $client->getContainer();

        $this->territoireRepository =
            $this->container->get('repository.territoire');
        $this->cacheInfoRepository =
            $this->container->get('repository.cache_info');
    }

    public function testgetLastModifiedAndInvalidate()
    {
        $pays = new Pays('France');
        $region = new Region($pays, 11, 'Île-de-France');
        $this->territoireRepository->add($region);
        $this->territoireRepository->save();

        $configDate =
            (new \DateTime())
            ->setTimestamp(
                (int) $this->container->getParameter('cache_invalidate_date')
            )
        ;
        $this->assertEquals(
            $configDate,
            $this->cacheInfoRepository->getLastModified($region)
        );

        $date = new \DateTime();
        $this->assertTrue($configDate < $date);

        $this->cacheInfoRepository->invalidate($region);
        $this->territoireRepository->save();

        $this->assertTrue(
            $this->cacheInfoRepository->getLastModified($region) >= $date
        );
    }
}
