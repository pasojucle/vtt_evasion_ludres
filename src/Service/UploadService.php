<?php

namespace App\Service;

use App\Service\LicenceService;
use App\DataTransferObject\User;
use App\Entity\User as EntityUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UploadService
{
    private ParameterBagInterface $params;
    private SluggerInterface $slugger;

    public function __construct(ParameterBagInterface $params, SluggerInterface $slugger)
    {
        $this->params = $params;
        $this->slugger = $slugger;
    }


    public function uploadFile($pictureFile, ?string $dir='uploads_directory_path'): ?string
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
}