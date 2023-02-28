<?php declare(strict_types=1);

namespace App\Service\API\PublicKey;

use DateTime;
use App\Entity\PublicKey;
use App\Service\UserService;
use App\Repository\UserRepository;
use App\Repository\PublicKeyRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class PublicKeyService
{
    public function __construct(
        private readonly PublicKeyRepository $publicKeyRepository,
        private readonly UserRepository $userRepository,
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

    public function deletePublicKey(
        string $uuid
    ): void
    {
        $user = $this->userService->getUserByUuidFromCache($uuid);
        $publicKey = $this->publicKeyRepository->findOneBy(['user' => $user]);

        $this->publicKeyRepository->deleteEntry($publicKey);
    }

    public function savePublicKey(
        string $uuid,
        string $key
    ): void
    {
        $user = $this->userRepository->findOneBy(['uuid' => $uuid]);

        $publicKey = new PublicKey();

        $publicKey
            ->setUser($user)
            ->setKey($key)
            ->setCreationDate(new DateTime())
        ;

        $this->publicKeyRepository->persistAndFlushEntity($publicKey);
    }

    public function updatePublicKey(
        string $uuid,
        string $key
    ): void
    {
        $user = $this->userService->getUserByUuidFromCache($uuid);
        $publicKey = $this->publicKeyRepository->findOneBy(['user' => $user]);

        $publicKey
            ->setKey($key)
            ->setCreationDate(new DateTime())
        ;

        $this->publicKeyRepository->flushEntity();
    }


}
