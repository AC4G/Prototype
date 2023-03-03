<?php declare(strict_types=1);

namespace App\Service\API\PublicKey;

use DateTime;
use App\Entity\PublicKey;
use App\Repository\UserRepository;
use App\Repository\PublicKeyRepository;

final class PublicKeyService
{
    public function __construct(
        private readonly PublicKeyRepository $publicKeyRepository,
        private readonly UserRepository $userRepository
    )
    {
    }

    public function deletePublicKey(
        string $uuid
    ): void
    {
        $user = $this->userRepository->getUserByUuidFromCache($uuid);
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
        $user = $this->userRepository->getUserByUuidFromCache($uuid);
        $publicKey = $this->publicKeyRepository->findOneBy(['user' => $user]);

        $publicKey
            ->setKey($key)
            ->setCreationDate(new DateTime())
        ;

        $this->publicKeyRepository->flushEntity();
    }


}
