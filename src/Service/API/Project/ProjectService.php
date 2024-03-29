<?php declare(strict_types=1);

namespace App\Service\API\Project;

use App\Entity\Project;
use App\Serializer\ProjectNormalizer;

final class ProjectService
{
    public function __construct(
        private readonly ProjectNormalizer $projectNormalizer
    )
    {
    }

    public function prepareData(
        array|Project $projects,
        string $context = null
    ): array
    {
        if (is_object($projects)) {
            return $this->projectNormalizer->normalize($projects, null, $context);
        }

        $projectList = [];

        foreach ($projects as $project) {
            $projectList[] = $this->projectNormalizer->normalize($project, null, $context);
        }

        return $projectList;
    }


}
