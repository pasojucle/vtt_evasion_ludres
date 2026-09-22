<?php

declare(strict_types=1);

namespace App\State\MemberStatus\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\MemberStatus\MemberStatusSheetView;
use App\Dto\View\MemberStatus\MemberStatusView;
use App\Entity\Member;
use App\Mapper\MemberStatus\MemberStatusReadMapper;

/**
 * @implements FormComponentProviderInterface<Member>
 */
class MemberStatusReadProvider implements FormComponentProviderInterface, TurboStreamProviderInterface
{
    public function __construct(
        private MemberStatusReadMapper $memberClubReadMapper,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): MemberStatusSheetView
    {
        return new MemberStatusSheetView(
            title: 'Modifier',
            description: 'Modifier le statut de l\'adhérent dans le club ',
            action: 'Modifier'
        );
    }

    public function getFormOptions(object $data): array
    {
        return [
            'attr' => [
                'data-action' => 'turbo:submit-end->sheet#handleFormSubmit',
            ],
        ];
    }

    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): MemberStatusView
    {
        return $this->memberClubReadMapper->mapToView($data);
    }
}
