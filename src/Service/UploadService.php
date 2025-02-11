<?php

declare(strict_types=1);

namespace App\Service;

use Error;
use GdImage;
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
        private ProjectDirService $projectDirService,
        private SluggerInterface $slugger,
        private FileService $fileService,
    ) {
    }

    public function uploadFile(?UploadedFile $pictureFile, ?string $dir = 'uploads_directory_path'): ?string
    {
        if ($pictureFile) {
            $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = sprintf('%s-%s.%s', $safeFilename, uniqid(), $this->getExtention($pictureFile));
            $directory = $this->projectDirService->path($dir);

            if (!is_dir($directory)) {
                mkdir($directory);
            }

            try {
                $pictureFile->move(
                    $directory,
                    $newFilename
                );
            } catch (FileException $e) {
                throw new Error($e->getMessage());
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

    public function resize(string $inputdir, string $filename, string $size, string $outputDir): bool
    {
        $inputPath = $this->projectDirService->path($inputdir, $filename);
        $outputPath = $this->projectDirService->path($outputDir, $filename);
        $this->mkdirIfNotExists($outputDir);
        list($originWidth, $originHeight, $type) = getimagesize($inputPath);
        $orientation = $this->getOrientation($originWidth, $originHeight);

        list($outputWidth, $outputHeight) = $this->getOutputSize($originWidth, $originHeight, $orientation, $size);

        $imageSrc = (IMAGETYPE_JPEG === $type) ? imagecreatefromjpeg($inputPath) : imagecreatefrompng($inputPath);

        $imageBlack = imagecreatetruecolor($outputWidth, $outputHeight);

        imagecopyresampled($imageBlack, $imageSrc, 0, 0, 0, 0, $outputWidth, $outputHeight, $originWidth, $originHeight);

        if (!$imageBlack = $this->imageRotate($inputPath, $imageBlack)) {
            return false;
        }

        if (!imagejpeg($imageBlack, $outputPath) || !imagepng($imageBlack, $outputPath)) {
            return false;
        }

        return true;
    }

    private function imageRotate(string $inputPath, GdImage $imageBlack): GdImage|false
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

    private function mkdirIfNotExists(string $outputDir): string
    {
        $outputPath = $this->projectDirService->path($outputDir);
        $filesystem = new Filesystem();
        if (!$filesystem->exists($outputPath)) {
            $filesystem->mkdir($outputPath, 0775);
        }

        return $outputPath;
    }
}
