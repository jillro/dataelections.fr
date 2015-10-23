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

namespace AppBundle\Tests\Repository;

use AppBundle\Domain\Election\Tests\Entity\ElectionRepositoryTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DoctrineElectionRepositoryTest extends WebTestCase
{
    use ElectionRepositoryTestTrait;

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

        $this->electionRepository =
            $client->getContainer()->get('repository.election');

        $this->echeanceRepository =
            $client->getContainer()->get('repository.echeance');

        $this->territoireRepository =
            $client->getContainer()->get('repository.territoire');
    }
}
