<?php

declare(strict_types=1);

namespace App\Service\FileLocation;

use App\Entity\Interface\UploadableInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class FileLocationResolver
{
    /**
     * @param iterable<AbstractFileLocation> $locations
     */
    public function __construct(
        #[TaggedIterator('app.file_location')]
        private iterable $locations
    ) {}

    public function resolveDirectory(string $directory): ?AbstractFileLocation
    {
        $cleanDirectoryName = str_replace('-', '_', $directory);

        /** @var AbstractFileLocation $location */
        foreach ($this->locations as $location) {
            if ($cleanDirectoryName === $location->getBaseDirectoryName()) {
                return $location;
            }
        }

        return null;
    }

    public function getDirectory(UploadableInterface $media): string
    {
        $className = get_class($media);

        foreach ($this->locations as $location) {
            if ($location->supports($className)) {
                return $location->getDirectory($media);
            }
        }

        throw new \InvalidArgumentException(sprintf('Aucun localisateur de fichier trouvé pour la classe "%s".', $className));
    }
}