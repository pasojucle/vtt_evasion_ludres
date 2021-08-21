<?php

namespace App\Service;

use App\Service\LicenceService;
use App\DataTransferObject\User;
use App\Entity\User as EntityUser;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UserService
{
    private ParameterBagInterface $params;
    private SluggerInterface $slugger;
    private LicenceService $licenceService;

    public function __construct(ParameterBagInterface $params, SluggerInterface $slugger, LicenceService $licenceService)
    {
        $this->params = $params;
        $this->slugger = $slugger;
        $this->licenceService = $licenceService;
    }

    public function convertPaginatorToUsers(Paginator $users): array
    {

        return $this->convertUsers($users);
    }

    public function convertArrayToUsers(Array $users): array
    {

        return $this->convertUsers($users);
    }

    public function convertToUser(EntityUser $user): User
    {

        return $usersDto[] = new User($user,
            $this->licenceService->getCurrentSeason(),
            $this->licenceService->getSeasonsStatus());
    }

    private function convertUsers($users): array
    {

        $usersDto = [];
        if (!empty($users)) {
            foreach ($users as $user){
               $usersDto[] = new User($user,
                $this->licenceService->getCurrentSeason(),
                $this->licenceService->getSeasonsStatus());
            }        
        }

        return $usersDto;
    }

    public function uploadFile($pictureFile): ?string
    {
        if ($pictureFile) {
            $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$pictureFile->guessExtension();
            if (!is_dir($this->params->get('uploads_directory'))) {
                mkdir($this->params->get('uploads_directory'));
            }
            try {
                $pictureFile->move(
                    $this->params->get('uploads_directory'),
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