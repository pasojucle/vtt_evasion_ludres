<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\ApiResource;
use App\State\UploadStateProcessor;
use App\State\UploadStateProvider;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    shortName: 'UploadFiles'
)]
#[Get(
    uriTemplate: '/uploads/{filename}',
    provider: UploadStateProvider::class,
    normalizationContext: ['groups' => ['uploadFile:read']]
)]
#[Post(
    uriTemplate: '/uploads',
    processor: UploadStateProcessor::class,
    normalizationContext: ['groups' => ['uploadFile:read']]
)]
class UploadFile
{
    public const PATH = 'data/uploads';

    #[Groups(['uploadFile:read'])]
    public ?string $filename = null;

    public ?UploadedFile $file = null;
}


