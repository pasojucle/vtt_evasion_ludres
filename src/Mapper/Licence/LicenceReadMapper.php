<?php

declare(strict_types=1);

namespace App\Mapper\Licence;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\Licence\LicenceView;
use App\Dto\View\LinkView;
use App\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LicenceReadMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
    ){}

    public function mapToView(User $entity): LicenceView
    {
        $licence = $entity->getLastLicence();
        $season = $licence->getSeason();
        $familiMember = $licence->getFamilyMember()?->getMember();

        return new LicenceView(
            id: $entity->getId(),
            licenceId: $licence->getId(),
            number: $entity->getLicenceNumber(),
            season: new BadgeView(
                sprintf('%s - %s', $season, $season - 1),
                ColorVariant::ACCENT,
            ),
            createdAt: $licence->getCreatedAt()?->format('m/d/Y') ?? 'Inconnue',
            state: $licence->getState()->trans($this->translator),
            category: $licence->getCategory()->trans($this->translator),
            yearlyCoverage: !$licence->getCurrentSeasonForm() 
                ? new BadgeView(
                    sprintf('Assurance %s manquante', $season),
                    ColorVariant::DESTRUCTIVE,
                )
                : null,
            coverage: $licence->getCoverage()->trans($this->translator),
            bikeType: $licence->getBikeType()->trans($this->translator),
            familyMember: $familiMember?->getIdentity()->getFullName(),
            familyMemberUrl: $familiMember 
                ? $this->urlGenerator->generate('admin_user_show', ['user' => $familiMember->getId()])
                : null,
            sendNuberLicenceAction:new LinkView(
                url: $this->urlGenerator->generate('admin_send_number_licence', ['member' => $entity->getId()]),
                icon: 'lucide:send',
                size: Size::SM,
                label: 'Envoyer le numéro de licence',
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', LinkView::TOP),
                ],
            ),
            editAction: new LinkView(
                url: $this->urlGenerator->generate('admin_licence_edit', ['user' => $entity->getId()]),
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