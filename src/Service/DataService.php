<?php

namespace App\Service;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DataService
{
    private array $encodedData = [];

    public function __construct(
        private NormalizerInterface $normalizer
    )
    {
    }

    public function buildUnifiedDataCollection(array $dataCollection, array $keys, array $unsetKeys, string $unsetParameter, bool $json = true): mixed
    {
        foreach ($dataCollection as $item => &$content) {
            $content = $this->normalizer->normalize($content);

            foreach ($content as $key => $value) {
                foreach ($keys as $searchKey) {
                    if ($key === $searchKey) {
                        $content[$key] = json_decode($value);
                    }
                }


                if ($key === $unsetParameter && count($unsetKeys) > 0) {
                    foreach ($value as $secondKey => $secondValue) {
                        foreach ($unsetKeys as $unsetKey) {
                            if ($secondKey === $unsetKey) {
                                unset($content[$key][$secondKey]);
                            }
                        }
                    }
                }

                $this->encodedData[$item] = $content;
            }
        }

        if($json) {
            return json_encode($this->encodedData);
        }

        return $this->encodedData;
    }
}