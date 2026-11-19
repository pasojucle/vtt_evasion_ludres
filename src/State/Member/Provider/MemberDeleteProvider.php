<?php

declare(strict_types=1);

namespace App\State\Member\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\DialogModalView;
use App\Entity\Member;
use App\Mapper\DestructiveModalMapper;
use App\Service\UserService;

/**
 * @implements FormComponentProviderInterface<Member>
 */
class MemberDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
        private UserService $userService,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(sprintf(
            'Etes vous certain de supprimer l\'utilisateur <b>%s</b> ?',
            $this->userService->getFullname($data)
        ));
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [
            'attr' => [
                'data-action' => 'turbo:submit-end->modal#handleFormSubmit',
            ]
        ];
    }
}
