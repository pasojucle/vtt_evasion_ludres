<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\SlideshowDirectory;

interface SlideshowDirectoryRepositoryInterface
{
    public function save(SlideshowDirectory $slideShowDirectory, bool $flush = true): void;

    public function remove(SlideshowDirectory $slideShowDirectory, bool $flush = true): void;

    public function findAllASC(): array;
}
