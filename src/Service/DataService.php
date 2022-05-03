<?php declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DataService
{
    public function __construct(
        private NormalizerInterface $normalizer
    )
    {
    }

    public function convertObjectToArray(
        array|object $data
    ): array
    {
        $convertedData = [];

        if (is_object($data)) {
            $data = [$data];
        }

        foreach ($data as $object) {
            $convertedData[] = $this->normalizer->normalize($object);
        }

        return $convertedData;
    }

    public function removeProperties(
        array $data,
        array $properties
    ): array
    {
        foreach ($data as &$object) {
            foreach ($properties as $property) {
                foreach ($object as $key => $parameter) {
                    if ($key === $property) {
                        unset($object[$key]);
                    }
                }
            }
        }

        return $data;
    }

    public function rebuildPropertyArray(
        array $data,
        string $key,
        array $requiredParameters
    ): array
    {
        $newProperty = [];

        foreach ($data as $objectKey => $object) {
            foreach ($object as $propertyKey => $property) {
                if ($key === $propertyKey) {
                    foreach ($property as $secondPropertyKey => $parameter) {
                        foreach ($requiredParameters as $requiredParameter) {
                            if ($secondPropertyKey === $requiredParameter) {
                                $newProperty[$secondPropertyKey] = $parameter;
                            }
                        }
                    }
                }
            }

            $data[$objectKey][$key] = $newProperty;
        }

        return $data;
    }

    public function convertPropertiesToJson(
        array $data,
        array $propertiesForConverting
    ): array
    {
        foreach ($data as &$object) {
            foreach ($object as $propertyKey => $property) {
                foreach ($propertiesForConverting as $propertyForConverting) {
                    if ($propertyKey === $propertyForConverting) {
                        $object[$propertyKey] = json_decode($property);
                    }
                }
            }
        }

        return $data;
    }

    public function rebuildArrayToOneValue(
        array $data,
        string $key,
        string $valueKey
    ): array
    {
        foreach ($data as &$object) {
            foreach ($object as $propertyKey => $property) {
                if ($propertyKey === $key) {
                    foreach ($property as $secondKey => $value) {
                        if ($secondKey === $valueKey) {
                            $object[$propertyKey] = $value;

                            continue 2;
                        }
                    }
                }
            }
        }

        return $data;
    }

}
