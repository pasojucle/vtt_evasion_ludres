<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Dto\ButtonDto;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\HtmlAttributDto;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class WikiMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
        
    }

    public function mapToView(string $dirName, RoundedVariant $rounded = RoundedVariant::ROUNDED): ButtonDto
    {
        return new ButtonDto(
            url: $this->urlGenerator->generate('wiki_show', ['directory' => $dirName]),
            title: 'wiki',
            icon: 'lucide:circle-help',
            variant: ColorVariant::DEFAULT,
            rounded: $rounded,
            htmlAttributes: [
                new HtmlAttributDto('target', '_blank'),
            ],
        );
    }
}
