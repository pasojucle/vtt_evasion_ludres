<?php

declare(strict_types=1);

namespace App\Mapper\Health;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\Health\HealthView;
use App\Dto\View\LinkView;;
use App\Entity\Health;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HealthReadMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ){}

    public function mapToView(Health $health): HealthView
    {
        return new HealthView(
            id: $health->getId(),
            medicalCertificateDate: $health->getMedicalCertificateDate()?->format('d/m/Y') ?? 'Aucun',
            content: $health->getContent() ?? 'Aucune pathologie signalée', 
            action: new LinkView(
                url: $this->urlGenerator->generate('admin_health_edit', ['health' => $health->getId()]),
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