<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploadService
{
    private ParameterBagInterface $params;

    private SluggerInterface $slugger;

    public function __construct(ParameterBagInterface $params, SluggerInterface $slugger)
    {
        $this->params = $params;
        $this->slugger = $slugger;
    }

    public function uploadFile(?UploadedFile $pictureFile, ?string $dir = 'uploads_directory_path'): ?string
    {
        if ($pictureFile) {
            $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$pictureFile->guessExtension();
            if (!is_dir($this->params->get($dir))) {
                mkdir($this->params->get($dir));
            }

            try {
                $pictureFile->move(
                    $this->params->get($dir),
                    $newFilename
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            return $newFilename;
        }

        return null;
    }

    public function resizeBackground(string $filename,array $positions, int $outputWidth, int $outputHeight, string $outputDir): bool
    {
        $path = $this->params->get('backgrounds_directory_path').$filename;
        list($width, $height,  $type) = getimagesize($path);
        $ratio = ($width > $height) ? $outputWidth / $width : $outputHeight / $height;

        if ($type == IMAGETYPE_JPEG) {
            $imageSrc = imagecreatefromjpeg($path);
        } else {
            $imageSrc = imagecreatefrompng($path);
        }
        $imageBlack = imagecreatetruecolor( $outputWidth, $outputHeight );
        $outputPath = $this->params->get('backgrounds_directory_path'). $outputDir . DIRECTORY_SEPARATOR. $filename;
        imagecopyresampled($imageBlack, $imageSrc, (int) round($positions['positionX']), (int) round($positions['positionY']), 0, 0, (int) round($width * $ratio), (int) round($height * $ratio), $width, $height );
        if (!imagejpeg ($imageBlack, $outputPath) || !imagepng ($imageBlack, $outputPath)) {
            return false;
        }
        return true;
    }
}
