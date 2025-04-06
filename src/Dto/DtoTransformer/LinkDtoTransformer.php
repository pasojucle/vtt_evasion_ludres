<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\LinkDto;
use App\Entity\Link;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LinkDtoTransformer
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function fromEntity(Link $link, bool $novelty = false): LinkDto
    {
        $linkDto = new LinkDto();
        $linkDto->image = $this->getImage($link);
        $linkDto->description = $link->getDescription();
        $linkDto->content = $link->getContent();
        $linkDto->btnShow = $this->getBtnShow($link);
        $linkDto->url = $link->geturl();
        $linkDto->novelty = $novelty;

        return $linkDto;
    }

    public function fromEntities(array $linkEntities, array $linkViewedIds = []): array
    {
        $links = [];
        foreach ($linkEntities as $linkEntity) {
            $links[] = $this->fromEntity($linkEntity, in_array($linkEntity->getId(), $linkViewedIds));
        }

        return $links;
    }

    private function getImage(Link $link): string
    {
        if (!$link->getImage() || str_contains($link->getImage(), 'http')) {
            return $link->getImage();
        }

        return $this->urlGenerator->generate('get_data_file', ['directory' => 'uploads_directory_path', 'filename' => base64_encode($link->getImage())]);
    }

    private function getBtnShow(Link $link): array
    {
        $target = ($link->getContent()) ? '_self' : '_blank';
        
        return ['url' => $this->urlGenerator->generate('link_show', ['link' => $link->getId()]), 'target' => $target];
    }
}
