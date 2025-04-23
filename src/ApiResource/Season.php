<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Dto\ChoiceDto;
use App\State\SeasonStateProvider;

#[ApiResource(shortName: 'Season')]
#[GetCollection(
    name: 'season_choices',
    output: ChoiceDto::class,
    provider: SeasonStateProvider::class,
)]
class Season
{
}
