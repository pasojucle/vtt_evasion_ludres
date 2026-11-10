<?php

declare(strict_types=1);

namespace App\Dto\View\Health;

use App\Core\Contract\View\ComponentViewInterface;
use App\Core\Contract\View\TurboStreamViewInterface;
use App\Dto\View\LinkView;

readonly class HealthView implements TurboStreamViewInterface, ComponentViewInterface
{
    public function __construct(
        public int $id,
        public ?string $medicalCertificateDate,
        public ?string $content,
        public LinkView $action,
    ) {
    }

    public function getStreamTemplate(): string
    {
        return 'health/admin/update.lazy.html.twig';
    }
    
    public function getName(): string
    {
        return 'view';
    }

    public function getTemplate(): string
    {
        return 'health/admin/show.html.twig';
    }
}
