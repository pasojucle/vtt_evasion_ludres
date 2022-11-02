<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\Level;

class LevelViewModel extends AbstractViewModel
{
    public ?Level $entity;

    public ?string $title;

    public ?int $type;

    public ?array $colors;

    public ?bool $accompanyingCertificat;

    public static function fromLevel(?Level $level)
    {
        $levelView = new self();
        $levelView->title = $level->getTitle();
        $levelView->type = $level->getType();
        $levelView->entity = $level;
        $levelView->colors = $levelView->getColors();
        $levelView->accompanyingCertificat = $level->isAccompanyingCertificat();

        return $levelView;
    }

    private function getColors(): ?array
    {
        if ($this->entity?->getColor()) {
            $background = $this->entity->getColor();
            list($r, $g, $b) = sscanf($background, '#%02x%02x%02x');
            $color = (0.3 * $r + 0.59 * $g + 0.11 * $b > 200) ? '#000' : '#fff';

            return ['color' => $color, 'background' => $background];
        }

        return null;
    }
}
