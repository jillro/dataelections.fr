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

namespace AppBundle\Repository;

use AppBundle\Domain\Territoire\Entity\Territoire\AbstractTerritoire;
use AppBundle\Domain\Territoire\Entity\Territoire\Commune;
use AppBundle\Domain\Territoire\Entity\Territoire\Departement;
use AppBundle\Domain\Territoire\Entity\Territoire\Pays;
use AppBundle\Domain\Territoire\Entity\Territoire\TerritoireRepositoryInterface;
use AppBundle\Domain\Territoire\Entity\Territoire\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException as DoctrineException;

class DoctrineTerritoireRepository implements TerritoireRepositoryInterface
{
    public function __construct($doctrine)
    {
        $this->em = $doctrine->getManager();
    }

    public function add(AbstractTerritoire $element)
    {
        $this->em->persist($element);
    }

    public function findLike($string, $limit = 10)
    {
        $queryResult = $this
            ->em
            ->createQuery(
                'SELECT territoires
                FROM
                    AppBundle\Domain\Territoire\Entity\Territoire\AbstractTerritoire
                    territoires
                WHERE
                    territoires.nom = :exactString'
            )
            ->setParameter('exactString', $string)
            ->setMaxResults($limit)
            ->getResult()
        ;
        $result = $queryResult;

        $queryResult = $this
            ->em
            ->createQuery(
                'SELECT territoires
                FROM
                    AppBundle\Domain\Territoire\Entity\Territoire\AbstractTerritoire
                    territoires
                WHERE
                    territoires.nom LIKE :stringAtStart
                    AND territoires.nom NOT LIKE :exactString
                ORDER BY territoires.nom'
            )
            ->setParameter('exactString', $string)
            ->setParameter('stringAtStart', $string.'%')
            ->setMaxResults($limit)
            ->getResult()
        ;

        $result = array_merge($result, $queryResult);

        $queryResult = $this
            ->em
            ->createQuery(
                'SELECT territoires
                FROM
                    AppBundle\Domain\Territoire\Entity\Territoire\AbstractTerritoire
                    territoires
                WHERE
                    territoires.nom LIKE :string
                    AND territoires.nom NOT LIKE :stringAtStart
                    AND territoires.nom NOT LIKE :exactString'
            )
            ->setParameter('exactString', $string)
            ->setParameter('stringAtStart', $string.'%')
            ->setParameter('string', '%'.$string.'%')
            ->setMaxResults($limit)
            ->getResult()
        ;

        $result = array_merge($result, $queryResult);
        $queriesResult = $result;
        foreach ($queriesResult as $key => $value) {
            if (count($result) >= $limit) {
                break;
            }
            if ($value instanceof Commune) {
                $result = array_merge($result, $value->getArrondissements()->toArray());
            }
            if ($value instanceof Departement) {
                $result = array_merge($result, $value->getCirconscriptionsLegislatives()->toArray());
            }
        }

        return array_slice($result, 0, $limit);
    }

    public function getArrondissementCommunal($commune, $codeArrondissement)
    {
        return $this
            ->em
            ->getRepository(
                '\AppBundle\Domain\Territoire\Entity'.
                '\Territoire\ArrondissementCommunal'
            )
            ->findOneBy(array(
                'commune' => $commune,
                'code' => $codeArrondissement,
            ));
    }

    public function getCirconscriptionEuropeenne($critere)
    {
        $result = $this
            ->em
            ->getRepository(
                '\AppBundle\Domain\Territoire\Entity'.
                '\Territoire\CirconscriptionEuropeenne'
            )
            ->findOneByCode($critere)
        ;

        return $result ? $result :
            $this
            ->em
            ->getRepository(
                '\AppBundle\Domain\Territoire\Entity'.
                '\Territoire\CirconscriptionEuropeenne'
            )
            ->findOneByNom($critere)
        ;
    }

    public function getCirconscriptionLegislative($codeDepartement, $code)
    {
        $query = $this
            ->em
            ->createQuery(
                'SELECT circo
                FROM \AppBundle\Domain\Territoire\Entity\Territoire\CirconscriptionLegislative
                circo
                JOIN circo.departement departement
                WHERE departement.code = :codeDepartement
                AND circo.code = :code'
            )
            ->setParameter('codeDepartement', $codeDepartement)
            ->setParameter('code', $code)
        ;

        return $query->getOneOrNullResult();
    }

    public function getCommune($codeDepartement, $codeCommune)
    {
        $query = $this
            ->em
            ->createQuery(
                'SELECT commune
                FROM \AppBundle\Domain\Territoire\Entity\Territoire\Commune
                commune
                JOIN commune.departement departement
                WHERE departement.code = :codeDepartement
                AND commune.code = :codeCommune'
            )
            ->setParameter('codeDepartement', $codeDepartement)
            ->setParameter('codeCommune', $codeCommune)
        ;

        return $query->getOneOrNullResult();
    }

    public function getDepartement($code)
    {
        return $this
            ->em
            ->getRepository(
                '\AppBundle\Domain\Territoire\Entity\Territoire'
                .'\Departement'
            )
            ->findOneByCode($code)
        ;
    }

    public function getPays()
    {
        $pays = $this
            ->em
            ->getRepository(
                '\AppBundle\Domain\Territoire\Entity\Territoire'
                .'\Pays'
            )
            ->findOneByNom('France')
        ;

        if (!$pays) {
            $entities = $this->em->getUnitOfWork()->getScheduledEntityInsertions();

            foreach ($entities as $entity) {
                if ($entity instanceof Pays) {
                    $pays = $entity;
                    break;
                }
            }
        }

        if (!$pays) {
            $pays = new Pays();
            $this->add($pays);
        }

        return $pays;
    }

    public function getRegion($code)
    {
        return $this
            ->em
            ->getRepository(
                '\AppBundle\Domain\Territoire\Entity\Territoire'
                .'\Region'
            )
            ->findOneByCode($code)
        ;
    }

    /**
     * Retire le territoire du repository si elle existe.
     *
     * @param AbstractTerritoire $element L'élection à retirer.
     */
    public function remove(AbstractTerritoire $element)
    {
        $this->em->remove($element);
    }

    /**
     * Enregistrer les changements dans le repository.
     */
    public function save()
    {
        try {
            $this->checkUniqueRules();
            $this->em->flush();
        } catch (DoctrineException $exception) {
            throw new UniqueConstraintViolationException(
                $exception->getMessage()
            );
        } catch (\Doctrine\DBAL\Exception\DriverException $exception) {
            throw new UniqueConstraintViolationException(
                $exception->getMessage()
            );
        }
    }

    private function checkUniqueRules()
    {
        $entities = $this->em->getUnitOfWork()->getScheduledEntityInsertions();

        foreach ($entities as $entity) {
            if (!$entity instanceof AbstractTerritoire) {
                continue;
            }
            $repo = $this->em->getRepository(get_class($entity));
            switch (get_class($entity)) {
                case 'AppBundle\Domain\Territoire\Entity'.
                '\Territoire\Commune':
                    $exist = $this->getCommune(
                        $entity->getDepartement()->getCode(),
                        $entity->getCode()
                    );
                    break;
                case 'AppBundle\Domain\Territoire\Entity'.
                '\Territoire\CirconscriptionLegislative':
                    $exist = $this->getCirconscriptionLegislative(
                        $entity->getDepartement()->getCode(),
                        $entity->getCode()
                    );
                    break;
                case 'AppBundle\Domain\Territoire\Entity'.
                '\Territoire\ArrondissementCommunal':
                    $exist = $this->getArrondissementCommunal(
                        $entity->getCommune(),
                        $entity->getCode()
                    );
                    break;
                case 'AppBundle\Domain\Territoire\Entity'.
                '\Territoire\CirconscriptionEuropeenne':
                    $exist = $this->getCirconscriptionEuropeenne(
                        $entity->getNom()
                    );
                    break;
                case 'AppBundle\Domain\Territoire\Entity'.
                '\Territoire\Pays':
                    $exist = $repo->findOneByNom('France');
                    break;
                default:
                    $exist = $repo->findOneByCode($entity->getCode());
            }

            if (null !== $exist) {
                throw new UniqueConstraintViolationException(
                    'Les communes doivent être unique par code et département'.
                    ', et les départements et régions doivent être uniques '.
                    'par code. Il existe déjà un territoire '.
                    $exist->getNom().', impossible de le remplacer par '.
                    $entity->getNom()
                );
            }
        }

        return true;
    }
}
