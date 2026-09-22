<?php

declare(strict_types=1);

namespace App\State\Gardian\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\Gardian\GardianSheetView;
use App\Dto\View\Gardian\GardianView;
use App\Entity\MemberGardian;
use App\Mapper\Gardian\GardianReadMapper;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements TurboStreamProviderInterface<MemberGardian>
 */
class GardianReadProvider implements FormComponentProviderInterface, TurboStreamProviderInterface
{
    public function __construct(
        private GardianReadMapper $gardianReadMapper,
        private TranslatorInterface $translator,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): GardianSheetView
    {
        return new GardianSheetView(
            title: 'Modifier',
            description: sprintf('Modifier le %s', $data->getKind()->trans($this->translator)),
            action: 'Modifier'
        );
    }

    public function getFormOptions(object $data): array
    {
        $member = $data->getMember();
        $licence = $member->getLastLicence();

        return [
            'category' => $licence->getCategory(),
            'is_yearly' => $licence->getState()->isYearly(),
            'attr' => [
                'data-controller' => 'form-modifier form-validator',
                'data-action' => 'turbo:submit-end->sheet#handleFormSubmit',
            ],
        ];
    }

    public function getStreamView(object $entity, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): GardianView
    {
        return $this->gardianReadMapper->mapToView($entity);
    }
}
