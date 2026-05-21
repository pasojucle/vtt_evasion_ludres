<?php

declare(strict_types=1);

namespace App\State\Member\Provider;

use App\Dto\DialogModalDto;
use App\Entity\Member;
use App\Mapper\DestructiveModalMapper;
use App\Service\UserService;

class MemberDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
        private UserService $userService,
    )
    {

    }
    public function mapToView(Member  $entity): DialogModalDto
    {

        return $this->destructiveModalMapper->mapToView(sprintf('Etes vous certain de supprimer l\'utilisateur <b>%s</b> ?', 
            $this->userService->getFullname($entity)
        ));
    }
}