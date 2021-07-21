<?php

namespace App\Service;

use App\DataTransferObject\User;
use App\Entity\User as EntityUser;
use Doctrine\ORM\Tools\Pagination\Paginator;


class UserService
{
    public function convertPaginatorToUsers(Paginator $users): array
    {

        return $this->convertUsers($users);
    }

    public function convertArrayToUsers(Array $users): array
    {

        return $this->convertUsers($users);
    }

    private function convertUsers($users): array
    {
        $usersDto = [];
        if (!empty($users)) {
            foreach ($users as $user){
               $usersDto[] = new User($user);
            }        
        }

        return $usersDto;
    }
}