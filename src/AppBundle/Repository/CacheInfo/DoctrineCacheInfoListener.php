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

namespace AppBundle\Repository\CacheInfo;

use AppBundle\Domain\Election\Entity\Election\ScoreAssignment;
use AppBundle\Domain\Election\Entity\Election\VoteInfoAssignment;
use Doctrine\ORM\Event;

class DoctrineCacheInfoListener
{
    private $container;

    private $toInvalidate = array();

    private $recursionMutex;

    private $switchedOff = false;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Switch off le listener.
     *
     * @param bool $bool True pour switch off le listener
     */
    public function switchOff($bool)
    {
        $this->switchedOff = $bool;
    }

    /**
     * Gets all the entities to flush.
     *
     * @param Event\OnFlushEventArgs $eventArgs Event args
     */
    public function onFlush(Event\OnFlushEventArgs $eventArgs)
    {
        if ($this->recursionMutex || $this->switchedOff) {
            return;
        }

        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        //Insertions
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $this->invalidate($entity);
        }

        //Updates
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $this->invalidate($entity);
        }

        //Deletions
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $this->invalidate($entity);
        }
    }

    public function postFlush(Event\PostFlushEventArgs $eventArgs)
    {
        if ($this->recursionMutex || $this->switchedOff) {
            return;
        }

        foreach ($this->toInvalidate as $territoire) {
            $this
                ->container
                ->get('repository.cache_info')
                ->invalidate($territoire)
            ;
        }
        $this->recursionMutex = true;
        $eventArgs->getEntityManager()->flush();
        $this->recursionMutex = false;
        $this->toInvalidate = array();
    }

    private function invalidate($entity)
    {
        if ($entity instanceof VoteInfoAssignment || $entity instanceof ScoreAssignment) {
            $this->toInvalidate[] = $entity->getTerritoire();
        }
    }
}
