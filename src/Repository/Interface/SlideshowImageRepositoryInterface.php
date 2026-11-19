<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\SlideshowImage;

interface SlideshowImageRepositoryInterface
{
    public function save(SlideshowImage $slideShowImage, bool $flush = true): void;

    public function remove(SlideshowImage $slideShowImage, bool $flush = true): void;
}
