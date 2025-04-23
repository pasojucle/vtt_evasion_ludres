<?php

declare(strict_types=1);

namespace App\Dto;

class UserCollectionDto
{
    public ?int $id;

    public string $fullName = '';

    public array $seasons = [];

    public array $member = [];

    public array $level = [];

    public array $permissions = [];

    public string $btnShow;

    public string $testingBikeRides = '';

    public string $boardMember = '';

    public array $actions;

    public bool $isBoardMember = false;
}
