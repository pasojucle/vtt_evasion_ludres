<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Enum\AgreementKindEnum;

class LicenceAgreementDto
{
    public ?int $id = null;
    
    public string $title = '';

    public ?bool $agreed = false;

    public string $toString = "";

    public array $toHtml = [];

    public string $content = '';

    public AgreementKindEnum $kind;
}
