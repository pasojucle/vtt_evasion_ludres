<?php

declare(strict_types=1);

namespace App\Core\Contract\Provider;


interface StreamActionExportableInterface
{
    public function streamExportContent(object $data): void;
}
