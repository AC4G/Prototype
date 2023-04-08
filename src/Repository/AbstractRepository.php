<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class AbstractRepository extends ServiceEntityRepository
{
    public function flushEntity()
    {
        $this->getEntityManager()->flush();
    }

    public function persistAndFlushEntity(
        Object $entity
    )
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

        public function persistEntity(
        Object $entity
    )
    {
        $this->getEntityManager()->persist($entity);
    }

    public function deleteEntry(
        Object $object
    )
    {
        $this->getEntityManager()->remove($object);
        $this->getEntityManager()->flush();
    }
    
    public function persistAndFlushEntities(
        array $entities
    ): void
    {
        $em = $this->getEntityManager();

        foreach ($entities as $entity) {
            $em->persist($entity);
        }

        $em->flush();
    }


}
