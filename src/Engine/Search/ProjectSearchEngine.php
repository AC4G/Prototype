<?php declare(strict_types=1);

namespace App\Engine\Search;

use App\Repository\ProjectRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class ProjectSearchEngine
{
    public function __construct(
        private ProjectRepository $projectRepository
    )
    {
    }

    public function search(
        ?string $phrase,
        ?UserInterface $user = null
    ): array
    {



    }
}