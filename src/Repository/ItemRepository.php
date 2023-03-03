<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Item;
use App\Serializer\ItemNormalizer;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends AbstractRepository
{
    public function __construct(
        private readonly ItemNormalizer $itemNormalizer,
        private readonly CacheInterface $cache,
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

    public function getItemFromCacheById(
        int|string $id
    ): null|Item
    {
        return $this->cache->get('item_' . $id, function (ItemInterface $item) use ($id) {
            $item->expiresAfter(86400);

            return $this->findOneBy(['id' => $id]);
        });
    }

    public function getItemFromCacheInJsonFormatById(
        int|string $id,
        string $context = null
    ): null|string
    {
        return $this->cache->get('item_' . $id . '_json' . $context, function (ItemInterface $item) use ($id, $context) {
            $item->expiresAfter(86400);

            return json_encode($this->itemNormalizer->normalize($this->findOneBy(['id' => $id]), null, $context));
        });
    }

    public function getItemParameterFromCacheById(
        int|string $id
    ): null|string
    {
        return $this->cache->get('item_' . $id . '_parameter', function (ItemInterface $item) use ($id) {
            $item->expiresAfter(86400);

            return $this->findOneBy(['id' => $id])->getParameter();
        });
    }

    public function getItemsFromCacheByUser(
        User $user
    ): array
    {
        return $this->cache->get('items_' . $user->getUuid(), function (ItemInterface $item) use ($user) {
            $item->expiresAfter(86400);

            return $this->findBy(['user' => $user]);
        });
    }


}
