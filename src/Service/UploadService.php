<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Interface\UploadableInterface;
use App\Service\FileLocation\FileLocationResolver;
use GdImage;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\Exception\InvalidArgumentException;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploadService
{
    public const HD = '1920x 1080';
    public const LANDSCAPE = 0;
    public const PORTRAIT = 1;

    public function __construct(
        private SluggerInterface $slugger,
        private FileService $fileService,
        private FileLocationResolver $resolver,
    ) {
    }

    public function uploadFile(
        ?UploadedFile $pictureFile, 
        UploadableInterface $media, 
        ?string $extension = null
    ): ?string
    {
        $mimeType = $pictureFile->getMimeType();
        $detectedExtension = strtolower($extension ?? $this->getExtention($pictureFile));

        $allowedMimeTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
            'application/pdf',
            'video/mp4', 'video/webm', 'video/quicktime', 'video/x-matroska',
            'application/gpx+xml', 'application/xml', 'text/xml', 'application/octet-stream',
        ];
        $allowedExtensions = [
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
            'pdf',
            'mp4', 'webm', 'mov', 'mkv',
            'gpx',
        ];

        if (!in_array($mimeType, $allowedMimeTypes, true) || !in_array($detectedExtension, $allowedExtensions, true)) {
            throw new RuntimeException('Le format de ce fichier n’est pas autorisé pour des raisons de sécurité.');
        }

        if ($pictureFile) {
            $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = sprintf('%s-%s.%s', $safeFilename, uniqid(), $extension ?? $this->getExtention($pictureFile));

            $directory = $this->resolver->getDirectory($media);
            $this->fileService->mkdirIfNotExists($directory);

            try {
                $pictureFile->move(
                    $directory,
                    $newFilename
                );
            } catch (FileException $e) {
                throw new RuntimeException($e->getMessage());
            }

            return $newFilename;
        }

        return null;
    }

    private function getExtention(UploadedFile $pictureFile): string
    {
        try {
            return $pictureFile->guessExtension();
        } catch (InvalidArgumentException) {
            return pathinfo($pictureFile->getClientOriginalName(), PATHINFO_EXTENSION);
        }
    }

    public function resize(string $origin, string $size): bool
    {
        $pathParts = pathinfo($origin);
        $filesystem = new Filesystem();
        $baseDir = $pathParts['dirname'];
        $baseName = $pathParts['basename'];
        $tmp = $this->fileService->join($baseDir, 'tmp');
        $copy = $this->fileService->join($tmp, $baseName);
        $this->fileService->mkdirIfNotExists($tmp);
        $filesystem->rename($origin, $copy);

        list($originWidth, $originHeight, $type) = getimagesize($copy);
        $orientation = $this->getOrientation($originWidth, $originHeight);

        list($outputWidth, $outputHeight) = $this->getOutputSize($originWidth, $originHeight, $orientation, $size);

        $imageSrc = (IMAGETYPE_JPEG === $type) ? imagecreatefromjpeg($copy) : imagecreatefrompng($copy);

        $imageBlack = imagecreatetruecolor($outputWidth, $outputHeight);

        imagecopyresampled($imageBlack, $imageSrc, 0, 0, 0, 0, $outputWidth, $outputHeight, $originWidth, $originHeight);

        if (!$imageBlack = $this->imageRotate($copy, $imageBlack)) {
            return false;
        }

        if (!imagejpeg($imageBlack, $origin) || !imagepng($imageBlack, $origin)) {
            return false;
        }

        $filesystem->remove($tmp);

        return true;
    }

    public function imageRotate(string $inputPath, GdImage $imageBlack): GdImage|false
    {
        if (function_exists('exif_read_data')) {
            $exif = exif_read_data($inputPath);
            if ($exif && array_key_exists('Orientation', $exif) && 1 !== $orientation = (int) $exif['Orientation']) {
                $deg = match ($orientation) {
                    3 => 180,
                    6 => 270,
                    8 => 90,
                    default => null
                };
                if ($deg) {
                    return imagerotate($imageBlack, $deg, 0);
                }
            }
        }

        return $imageBlack;
    }

    private function getOrientation(int $originWidth, int $originHeight): int
    {
        return ($originHeight < $originWidth) ? self::LANDSCAPE : self::PORTRAIT;
    }

    private function getOutputSize(int $originWidth, int $originHeight, int $orientation, string $size): array
    {
        list($width, $height) = explode('x', $size);
        if (self::PORTRAIT === $orientation) {
            list($width, $height) = [$height, $width];
        }
        $ratio = ((int) $width / (int) $height < $originWidth / $originHeight)
        ? (int) $width / $originWidth
        : (int) $height / $originHeight;

        return [(int) round($originWidth * $ratio), (int) round($originHeight * $ratio)];
    }

    public function getMaxAllowedUploadSize(): array
    {
        $configOptions = ['upload_max_filesize', 'post_max_size', 'memory_limit'];

        $values = [];
        $sizeInBytes = [];
        foreach ($configOptions as $option) {
            $values[$option] = ('-1' === ini_get($option)) ? '64M' : ini_get($option);
            $bytes = $this->fileService->humanToBytes($values[$option]);
            if ($bytes) {
                $sizeInBytes[$option] = $bytes;
            }
        }

        $minOption = array_search(min($sizeInBytes), $sizeInBytes);

        return ['value' => $values[$minOption], 'toBytes' => $sizeInBytes[$minOption]];
    }
}
