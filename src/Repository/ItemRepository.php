<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Item;
use App\Serializer\ItemNormalizer;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\InputBag;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends AbstractRepository
{
    public function __construct(
        private readonly UserRepository $userRepository,
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
        int|string $id
    ): null|string
    {
        return $this->cache->get('item_' . $id . '_json', function (ItemInterface $item) use ($id) {
            $item->expiresAfter(86400);

            return json_encode($this->itemNormalizer->normalize($this->findOneBy(['id' => $id])));
        });
    }

    public function getItemParameterFromCacheById(
        int|string $id
    ): null|string
    {
        $item = $this->getItemFromCacheById($id);

        return $item->getParameter();
    }

    private function getItemIdsByUuid(
        string $uuid
    ): array
    {
        $user = $this->userRepository->getUserByUuidFromCache($uuid);

        $query = $this->createQueryBuilder(alias: 'item')
            ->select('item.id')
            ->where('item.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
        ;

        return $query->getArrayResult();
    }

    public function getItemIdsFromCacheByUuid(
        string $uuid
    ): array
    {
        return $this->cache->get('items_' . $uuid . '_list', function (ItemInterface $item) use ($uuid) {
            $item->expiresAfter(604800);

            return $this->getItemIdsByUuid($uuid);
        });
    }

    public function getItemsByList(
        array $itemIdList
    ): array
    {
        $items = [];

        foreach ($itemIdList as $item) {
            $items[] = $this->getItemFromCacheById($item['id']);
        }

        return $items;
    }

    public function getItemIdList(
        InputBag $inputBag,
        array $limitAndOffset,
        User $user = null
    ): array
    {
        $queryBuilder = $this->createQueryBuilder(alias: 'item')
            ->select('item.id')
            ->setMaxResults($limitAndOffset['limit'])
            ->setFirstResult($limitAndOffset['offset']);
        ;

        if (!is_null($user)) {
            $queryBuilder
                ->andWhere('item.user = :user')
                ->setParameter('user', $user);
        }

        $projectName = $inputBag->get('projectName');
        $creator = $inputBag->get('creator');
        $query = $inputBag->get('q');

        if ((bool)$inputBag->get('filter') === false || (is_null($projectName) && is_null($query) && (is_null($creator) && is_null($user)))) {
            if (!is_null($user)) {
                return $this->getItemIdsFromCacheByUuid($user->getUuid());
            }

            return $queryBuilder->getQuery()->getArrayResult();
        }

        if (!is_null($projectName)) {
            $queryBuilder
                ->join('item.project', 'proj')
                ->andWhere('proj.projectName = :projectName')
                ->setParameter('projectName', $projectName);
        }

        if (!is_null($creator) && is_null($user)) {
            $queryBuilder
                ->andWhere('item.user = :creator')
                ->setParameter('creator', $this->userRepository->getUserByUuidOrNicknameFromCache($creator));
        }

        if (!is_null($query) && mb_strlen($query) > 4) {
            $queryBuilder
                ->andWhere('MATCH (item.parameter, item.parameter) AGAINST (:query IN BOOLEAN MODE) > 0')
                ->setParameter('query', $query);
        }

        if (!is_null($query) && mb_strlen($query) <= 4) {
            $queryBuilder
                ->andWhere('item.name LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        return $queryBuilder->getQuery()->getArrayResult();
    }


}
