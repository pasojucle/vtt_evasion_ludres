<?php

declare(strict_types=1);

namespace App\State\Gardian\Provider;

use App\Dto\View\Gardian\GardianSheetView;
use App\Dto\View\Gardian\GardianView;
use App\Entity\MemberGardian;
use App\Mapper\Gardian\GardianReadMapper;
use App\State\Interface\TurboStreamProviderInterface;


/**
 * @implements TurboStreamProviderInterface<MemberGardian>
 */
class GardianReadProvider implements TurboStreamProviderInterface
{
    public function __construct(
        private GardianReadMapper $gardianReadMapper,
    ) {
    }

    public function getFormView(object $entity, ?string $fallback = null): GardianSheetView
    {
        return new GardianSheetView(
            title: 'Modifier',
            description: 'blabla',
            action: 'Modifier'
        );
        
    }

    public function getFormOptions(object $entity): array
    {   
        $member = $entity->getMember();
        $licence = $member->getLastLicence();

        return [
            'category' => $licence->getCategory(),
            'is_yearly' => $licence->getState()->isYearly(),
            'attr' => [
                'data-controller' => 'form-modifier form-validator',
            ],
        ];
    }

    public function getStreamView(object $entity): GardianView
    {

        return $this->gardianReadMapper->mapToView($entity);
    }
}
