<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Type;

use App\Entity\Enum\ContentKindEnum;

class ContentKindEnumType extends EnumType
{
    protected function getEnum(): string
    {
        return ContentKindEnum::class;
    }

    public function getName(): string
    {
        return 'ContentType';
    }
}
