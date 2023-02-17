<?php declare(strict_types=1);

namespace App\Service\API\PublicKey;

use App\Entity\PublicKey;
use App\Service\API\UserService;
use App\Repository\PublicKeyRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class PublicKeyService
{
    public function __construct(
        private readonly PublicKeyRepository $publicKeyRepository,
        private readonly UserService $userService,
        private readonly CacheInterface $cache
    )
    {
    }

    public function getPublicKeyByUuidFromCache(
        string $uuid
    ): null|PublicKey
    {
        $user = $this->userService->getUserByUuidFromCache($uuid);

        return $this->cache->get('public_key_' . $uuid, function (ItemInterface $item) use ($user) {
            $item->expiresAfter(86400);

            return $this->publicKeyRepository->findOneBy(['user' => $user]);
        });
    }


}
