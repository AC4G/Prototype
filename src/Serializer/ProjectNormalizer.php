<?php declare(strict_types=1);

namespace App\Serializer;

use App\Entity\Project;

final class ProjectNormalizer
{
    public function normalize(
        Project $project,
        string $format = null,
        string $context = null
    ): array
    {
        if ($context === 'public') {
            return [
                'projectName' => $project->getProjectName()
            ];
        }

        return [
            'id' => $project->getId(),
            'projectName' => $project->getProjectName(),
            'organisationName' => $project->getOrganisationName(),
            'organisationEmail' => $project->getOrganisationEmail(),
            'organisationLogo' => $project->getOrganisationLogo(),
            'supportEmail' => $project->getSupportEmail(),
            'creationDate' => $project->getCreationDate()
        ];
    }

    public function supportsNormalization(
        $data,
        string $format = null,
        string $context = null
    ): bool
    {
        return $data instanceof Project;
    }
}
