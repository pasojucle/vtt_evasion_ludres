<?php

declare(strict_types=1);

namespace App\State\Cluster\Provider;

use App\Core\Contract\Provider\StreamDownloadProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Filter\AbstractFilter;
use App\Entity\Cluster;
use App\Mapper\Cluster\ParticpantListExportMapper;
use App\Service\LogService;
use App\Service\StringService;

/**
 * @implements StreamDownloadProviderInterface<Cluster>
 */
class ClusterExportProvider implements StreamDownloadProviderInterface
{
    public function __construct(
        private ParticpantListExportMapper $particpantListExportMapper,
        private LogService $logService,
        private StringService $stringService,
    ) {
    }

    public function streamDownloadContent(?object $data, ?HandlerContext $context = null): void
    {
        $this->logService->writeFromEntity($data);

        $this->particpantListExportMapper->streamToPdf($data);
    }

    public function basename(?object $data, ?HandlerContext $context = null): string
    {
        return $this->stringService->clean(
            sprintf(
                '%s-%s',
                $data->getTitle(),
                $data->getBikeRide()->getStartAt()->format('Ymd')
            )
        ) . '.pdf';
    }

    public function contentType(): string
    {
        return 'aplication\pdf';
    }
}
