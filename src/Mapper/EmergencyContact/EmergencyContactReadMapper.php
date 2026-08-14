<?php

declare(strict_types=1);

namespace App\Mapper\EmergencyContact;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\EmergencyContact\EmergencyContactView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LinkView;
use App\Dto\View\PhoneView;
use App\Entity\EmergencyContact;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmergencyContactReadMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function mapToView(EmergencyContact $entity): EmergencyContactView
    {
        return new EmergencyContactView(
            id: $entity->getId(),
            kinship: $entity->getKinship(),
            phone: new PhoneView($entity->getPhone()),
            action: new LinkView(
                url: $this->urlGenerator->generate('admin_emergency_contact_edit', ['emergencyContact' => $entity->getId()]),
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
