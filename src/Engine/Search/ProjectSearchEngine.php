<?php declare(strict_types=1);

namespace App\Engine\Search;

use App\Repository\ProjectRepository;
use App\Service\API\Project\ProjectService;
use Symfony\Component\Security\Core\User\UserInterface;

final class ProjectSearchEngine extends AbstractSearchEngine
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private readonly ProjectService $projectService
    )
    {
        parent::__construct(
            $this->projectService
        );
    }

    public function search(
        array $query,
        ?UserInterface $user = null
    ): array
    {
        return [];
    }


}
