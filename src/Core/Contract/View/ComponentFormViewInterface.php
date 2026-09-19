<?php

declare(strict_types=1);

namespace App\Core\Contract\View;

interface ComponentFormViewInterface
{
    public function getName(): string;
    
    public function getTemplate(): string;
    
    public function getFormAttr(): array;
}
