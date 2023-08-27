<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploadService
{
    public function __construct(
        private ProjectDirService $projectDirService,
        private SluggerInterface $slugger,
    ) {
    }

    public function uploadFile(?UploadedFile $pictureFile, ?string $dir = 'uploads_directory_path'): ?string
    {
        if ($pictureFile) {
            $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $pictureFile->guessExtension();
            $directory = $this->projectDirService->path($dir);
            
            if (!is_dir($directory)) {
                dump($directory);
                mkdir($directory);
            }

            try {
                $pictureFile->move(
                    $directory,
                    $newFilename
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            return $newFilename;
        }

        return null;
    }
}
