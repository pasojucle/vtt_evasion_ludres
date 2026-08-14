<?php

declare(strict_types=1);

namespace App\Mapper\LicenceAuthorization;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LicenceAuthorization\LicenceAuthorizationsView;
use App\Dto\View\LinkView;
use App\Entity\Licence;
use App\Entity\LicenceAgreement;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LicenceAuthorizationsReadMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private LicenceAuthorizationMapper $LicenceAuthorizationMapper
    ) {
    }

    public function mapToView(Licence $entity): LicenceAuthorizationsView
    {
        return new LicenceAuthorizationsView(
            $entity->getId(),
            array_map(
                fn (LicenceAgreement $licenceAgreement) => $this->LicenceAuthorizationMapper->mapToView($licenceAgreement),
                $entity->getLicenceAuthorizations()
            ),
            new LinkView(
                $this->urlGenerator->generate('admin_licence_authorizations_edit', ['licence' => $entity->getId()]),
                variant: ColorVariant::GOST,
                icon: 'lucide:pencil',
                size: Size::ICON,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', LinkView::SHEET_CONTENT),
                ],
            )
        );
    }
}
