<?php declare(strict_types=1);

namespace App\Serializer;

use App\Entity\Item;
use App\Serializer\ProjectNormalizer;

class ItemNormalizer
{
    public function __construct(
        private ProjectNormalizer $projectNormalizer
    )
    {
    }

    public function normalize(
        Item $item,
        string $format = null,
        array $context = []
    ): array
    {
        return [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'project' => is_null($item->getProject()) ? null : $this->projectNormalizer->normalize($item->getProject(), null, in_array('pagination', $context) ? ['pagination'] : []),
            'parameter' => json_decode($item->getParameter(), true),
            'path' => json_decode($item->getPath(), true),
            'creationDate' => $item->getCreationDate(),
            'creator' => [
                'id' => $item->getUser()->getId(),
                'nickname' => $item->getUser()->getNickname()
            ]
        ];
    }

    public function supportsNormalization(
        $data,
        string $format = null,
        array $context = []
    ): bool
    {
        return $data instanceof Item;
    }
}
