<?php

declare(strict_types=1);

namespace App\State\Cluster\Provider;

use App\Core\Contract\Provider\StreamActionExportableInterface;
use App\Mapper\Cluster\ParticpantListExportMapper;
use App\Service\LogService;

class ClusterExportProvider implements StreamActionExportableInterface
{
    public function __construct(
        private ParticpantListExportMapper $particpantListExportMapper,
        private LogService $logService,
    ){}

    public function streamExportContent(object $data): void
    {
        $this->logService->writeFromEntity($data);

        $this->particpantListExportMapper->streamToPdf($data);
    }
}