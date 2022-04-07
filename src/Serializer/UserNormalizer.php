<?php

namespace App\Serializer;

use App\Entity\User;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class UserNormalizer
{
    public function __construct(
        private ObjectNormalizer $normalizer
    )
    {
    }

    public function normalize(object $object, string $format, array $context)
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof User;
    }
}