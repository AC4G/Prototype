<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, Item::class
        );
    }

    public function getNameAndParameter(
        int $id
    )
    {
        $query = $this->createQueryBuilder(alias: 'item')
            ->select('item.parameter', 'item.name')
            ->where('item.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
        ;

        return $query->getSingleResult();
    }

    public function updateNameAndParameter(
        int $id,
        array $data
    )
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->update('App:Item', 'item')
            ->set('item.name', ':name')
            ->set('item.parameter', ':parameter')
            ->where('item.id = :id')
            ->setParameter('name', $data['name'])
            ->setParameter('parameter', $data['parameter'])
            ->setParameter('id', $id)
            ->getQuery()
        ;

        $query->execute();
    }

    public function updateParameter(
        int $id,
        array $parameter
    )
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->update('App:Item', 'item')
            ->set('item.parameter', ':parameter')
            ->where('item.id = :id')
            ->setParameter('parameter', json_encode($parameter))
            ->setParameter('id', $id)
            ->getQuery()
        ;

        $query->execute();
    }


}
