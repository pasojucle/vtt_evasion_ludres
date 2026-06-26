<?php

declare(strict_types=1);

namespace App\State\Member\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\Member;
use App\Mapper\DestructiveModalMapper;
use App\Service\UserService;
use App\State\FormComponentProviderInterface;

class MemberDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
        private UserService $userService,
    ) {
    }

    /**
     * @implements FormComponentProviderInterface<Member>
     */
    public function mapToView(object $entity): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(sprintf(
            'Etes vous certain de supprimer l\'utilisateur <b>%s</b> ?',
            $this->userService->getFullname($entity)
        ));
    }
}
