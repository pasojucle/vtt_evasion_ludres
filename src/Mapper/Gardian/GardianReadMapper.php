<?php

declare(strict_types=1);

namespace App\Mapper\Gardian;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\EmailView;
use App\Dto\View\Gardian\GardianView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LinkView;
use App\Dto\View\PhoneView;
use App\Entity\MemberGardian;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class GardianReadMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
    ){}

    public function mapToView(MemberGardian $entity): GardianView
    {
        $identity = $entity->getIdentity();
        $address = $identity->getAddress() ?? $entity->getMember()->getIdentity()->getAddress();

        return new GardianView(
            id: $entity->getId(),
            kind: $entity->getKind()->trans($this->translator),
            fullName: $identity->getFullName(),
            address: $address->getStreet(),
            city: sprintf('%s %s',$address->getCommune()->getPostalCode(), $address->getCommune()->getName()),
            email: new EmailView($identity->getEmail()),
            phones: array_map(fn ($phone) => new PhoneView($phone),
             array_filter([$identity->getMobile(), $identity->getPhone()])),
            action: new LinkView(
                url: $this->urlGenerator->generate('admin_gardian_edit', ['gardian' => $entity->getId()]),
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