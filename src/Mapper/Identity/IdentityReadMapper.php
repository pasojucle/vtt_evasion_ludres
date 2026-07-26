<?php

declare(strict_types=1);

namespace App\Mapper\Identity;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\EmailView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\Identity\IdentityView;
use App\Dto\View\LinkView;
use App\Dto\View\PhoneView;
use App\Entity\Address;
use App\Entity\Identity;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class IdentityReadMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private PassportPhotoMapper $passportPhotoMapper,
    ){}

    public function mapToView(Identity $identity, Address $address): IdentityView
    {
        $birthPlace = $identity->getBirthCommune()
            ? sprintf('%s (%s - France)', $identity->getBirthCommune()->getName(), $identity->getBirthCommune()->getDepartment()->getName())
            : sprintf('%s (%s)', $identity->getBirthPlace(), $identity->getBirthCountry());

        return new IdentityView(
            id: $identity->getId(),
            fullName: $identity->getFullName(),
            birthDate: $identity->getBirthDate()->format('d/m/Y'), 
            birthPlace: $birthPlace,
            address: $address->getStreet(),
            city: sprintf('%s %s',$address->getCommune()->getPostalCode(), $address->getCommune()->getName()),
            email: new EmailView($identity->getEmail()),
            phones: array_map(fn ($phone) => new PhoneView($phone),
             array_filter([$identity->getMobile(), $identity->getPhone()])),
            passportPhoto: $this->passportPhotoMapper->mapToView($identity->getFilename()),
            profession: $identity->getProfession(),
            action: new LinkView(
                url: $this->urlGenerator->generate('admin_identity_edit', ['identity' => $identity->getId()]),
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