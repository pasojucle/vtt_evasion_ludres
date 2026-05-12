<?php

declare(strict_types=1);

namespace App\Mapper;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class FilterMapper
{
    public function __construct(
        private DenormalizerInterface $denormalizer
    ) {
    }


    public function mapToDto(array $data, string $objectClass): object
    {
        return $this->denormalizer->denormalize($data, $objectClass, null, [
            AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true,
        ]);
    }
}
