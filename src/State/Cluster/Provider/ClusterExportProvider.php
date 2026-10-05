<?php

declare(strict_types=1);

namespace App\State\Cluster\Provider;

use App\Core\Contract\Provider\StreamActionExportableInterface;
use App\Entity\Cluster;
use App\Mapper\Cluster\ParticpantListExportMapper;
use App\Service\LogService;
use App\Service\StringService;

/**
 * @implements StreamActionExportableInterface<Cluster>
 */
class ClusterExportProvider implements StreamActionExportableInterface
{
    public function __construct(
        private ParticpantListExportMapper $particpantListExportMapper,
        private LogService $logService,
        private StringService $stringService,
    ){}

    public function streamExportContent(object $data): void
    {
        $this->logService->writeFromEntity($data);

        $this->particpantListExportMapper->streamToPdf($data);
    }

    public function basename(object $data): string
    {
        return $this->stringService->clean(
            sprintf('%s-%s', $data->getTitle(), 
            $data->getBikeRide()->getStartAt()->format('Ymd'))
        ). '.pdf';
    }
}