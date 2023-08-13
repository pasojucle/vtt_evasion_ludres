<?php

declare(strict_types=1);

namespace App\Dto;


class PaginatorDto
{
    public ?int $currentPage;

    public ?int $lastPage;

    public ?int $total;

    public array $pages = [];

    public ?array $previous;

    public ?array $next;

    public ?array $first;

    public ?array $last;
}
