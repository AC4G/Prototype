<?php declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DataService
{
    private array $processedData;

    public function __construct(
        private NormalizerInterface $normalizer
    )
    {
    }

    public function convertObjectToArray(
        array|object $dataCollection
    ): self
    {
        if (is_object($dataCollection)) {
            $dataCollection = [$dataCollection];
        }

        foreach ($dataCollection as $object) {
            $this->processedData[] = $this->normalizer->normalize($object);
        }

        return $this;
    }

    public function removeProperties(
        array $properties
    ): self
    {
        foreach ($this->processedData as &$object) {
            foreach ($properties as $property) {
                foreach ($object as $key => $parameter) {
                    if ($key === $property) {
                        unset($object[$key]);
                    }
                }
            }
        }

        return $this;
    }

    public function rebuildPropertyArray(
        string $key,
        array $requiredParameters
    ): self
    {
        $newProperty = [];

        foreach ($this->processedData as $objectKey => $object) {
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

            $this->processedData[$objectKey][$key] = $newProperty;
        }

        return $this;
    }

    public function convertPropertiesToJson(
        array $propertiesForConverting
    ): self
    {
        foreach ($this->processedData as &$object) {
            foreach ($object as $propertyKey => $property) {
                foreach ($propertiesForConverting as $propertyForConverting) {
                    if ($propertyKey === $propertyForConverting) {
                        $object[$propertyKey] = json_decode($property);
                    }
                }
            }
        }

        return $this;
    }

    public function rebuildArrayToOneValue(
        string $key,
        string $valueKey
    ): self
    {
        foreach ($this->processedData as &$object) {
            foreach ($object as $propertyKey => $property) {
                if ($propertyKey === $key) {
                    foreach ($property as $secondKey => $value) {
                        if ($secondKey === $valueKey) {
                            $object[$propertyKey] = $value;

                            continue 3;
                        }
                    }
                }
            }
        }

        return $this;
    }

    public function reset()
    {
        $this->processedData = [];
    }

    public function getJson(): ?string
    {
        return json_encode($this->processedData);
    }

    public function getArray(): ?array
    {
        return $this->processedData;
    }


}
