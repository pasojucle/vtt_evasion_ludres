<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LinkView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WikiMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function mapToView(string $dirName): LinkView
    {
        return new LinkView(
            url: $this->urlGenerator->generate('wiki_show', ['directory' => $dirName]),
            label: 'Aide en ligne',
            icon: 'lucide:circle-help',
            variant: ColorVariant::DROPDOWN,
            htmlAttributes: [
                new HtmlAttributeView('target', '_blank'),
            ],
        );
    }
}
