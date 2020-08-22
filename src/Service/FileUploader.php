<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private $targetDirectory;
    private $slugger;

    public function __construct($targetDirectory, SluggerInterface $slugger)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
    }

    public function upload(UploadedFile $file, ?int $width = null, ?int $height = null)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileName = $this->slugger->slug($originalFilename).'.'.$file->guessExtension();

        if (null === $width && null === $height) {
            try {
                $file->move($this->getTargetDirectory(), $fileName);
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }
        }

        if (null !== $width || null !== $height) {
            list($fileWidth, $fileHeight) = getimagesize($file);

            $ratio = 1;
            if (null !== $height) {
                $ratio = $height / $fileHeight;
            }
            if (null !== $width) {
                $ratio = $width / $fileWidth;
            }
            
            $dstX = 0;
            $dstY = 0;

            if ($file->getClientMimeType() == 'image/jpeg') {
                $imageSrc = imagecreatefromjpeg($file);
                $functionImage = 'imagejpeg';
            } else {
                $imageSrc = imagecreatefrompng($file);
                $functionImage = 'imagepng';
            }

            $imageBlack = imagecreatetruecolor( round($fileWidth * $ratio), round($fileHeight * $ratio) );
            imagecopyresampled($imageBlack, $imageSrc, $dstX, $dstY, 0, 0, round($fileWidth * $ratio), round($fileHeight * $ratio), $fileWidth, $fileHeight);
        
            try {
                $functionImage($imageBlack, $this->getTargetDirectory().DIRECTORY_SEPARATOR.$fileName);
            } catch (FileException $e) {
                dump('error');
                // ... handle exception if something happens during file upload
            }
        }

        return $fileName;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}