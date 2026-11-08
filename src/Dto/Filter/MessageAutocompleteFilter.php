<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Entity\Section;

class MessageAutocompleteFilter extends AbstractFilter
{
    public function __construct(
        public ?Section $section = null,
    ) {
    }
}
