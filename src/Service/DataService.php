<?php

namespace App\Service;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DataService
{
    private array $processedData = [];

    public function __construct(
        private NormalizerInterface $normalizer
    )
    {
    }

    public function convertObjectToArray(array $dataCollection): self
    {
        foreach ($dataCollection as  $item) {
            $this->processedData[] = $this->normalizer->normalize($item);
        }

        return $this;
    }

    public function removeProperties(array $properties): self
    {
        foreach ($this->processedData as $itemKey => &$item) {
            foreach ($properties as $property) {
                foreach ($item as $key => $parameter) {
                    if ($key === $property) {
                        unset($item[$key]);
                    }
                }
            }
        }

        return $this;
    }

    public function rebuildPropertyArray(string $key, array $requieredParameters): self
    {
        $newProperty = [];

        foreach ($this->processedData as $itemKey => $item) {
            foreach ($item as $propertyKey => $property) {
                if ($key === $propertyKey) {
                    foreach ($property as $secondPropertyKey => $parameter) {
                        foreach ($requieredParameters as $requieredParameter) {
                            if ($secondPropertyKey === $requieredParameter) {
                                $newProperty = [$secondPropertyKey => $parameter];
                            }
                        }
                    }
                }
            }

            $this->processedData[$itemKey][$key] = $newProperty;
        }

        return $this;
    }

    public function convertPropertiesToJson(array $propertiesForConverting): self
    {
        foreach ($this->processedData as $itemKey => $item) {
            foreach ($item as $propertyKey => $property) {
                foreach ($propertiesForConverting as $propertyForConverting) {
                    if ($property === $propertyForConverting) {
                        $item[$propertyKey] = json_decode($property);
                    }
                }
            }
        }

        return $this;
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