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

        $organisation = $project->getOrganisation();

        return [
            'id' => $project->getId(),
            'projectName' => $project->getProjectName(),
            'organisationName' => $organisation->getOrganisationName(),
            'organisationEmail' => $organisation->getOrganisationEmail(),
            'organisationLogo' => $organisation->getOrganisationLogo(),
            'supportEmail' => $organisation->getSupportEmail(),
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
