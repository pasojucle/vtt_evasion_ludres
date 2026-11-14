<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\SecondHandCategory;

interface SecondHandCategoryRepositoryInterface
{
    public function save(SecondHandCategory $secondHandCategory, bool $flush = true): void;

    public function remove(SecondHandCategory $secondHandCategory, bool $flush = true): void;
}
