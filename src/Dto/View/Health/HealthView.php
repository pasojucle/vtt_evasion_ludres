<?php

declare(strict_types=1);

namespace App\Dto\View\Health;

use App\Dto\View\EmailView;
use App\Dto\View\Interface\TurboStreamViewInterface;
use App\Dto\View\LinkView;
use App\Dto\View\PhoneView;


readonly class HealthView implements TurboStreamViewInterface
{
    public function __construct(
        public int $id,
        public ?string $medicalCertificateDate,
        public ?string $content,
        public LinkView $action,
    ) {
    }
}
