<?php declare(strict_types=1);

namespace App\Engine\Search;

use Symfony\Component\Security\Core\User\UserInterface;

interface InterfaceSearchEngine
{
    public function search(
        ?string $phrase,
        ?UserInterface $user = null
    ): array;
}