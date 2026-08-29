<?php

declare(strict_types=1);

namespace App\State\Gardian\Processor;

use App\Dto\State\TurboStreamProcessorResult;
use App\Entity\MemberGardian;
use App\State\Interface\FormTurboStreamProcessorInterface;
use App\UseCase\v2\Gardian\UpdateGardian;

/**
 * @implements FormTurboStreamProcessorInterface<MemberGardian>
 */
class GardianUpdateProcessor implements FormTurboStreamProcessorInterface
{
    public function __construct(
        private UpdateGardian $updateGardian,
    ) {
    }

    public function process(object $payload, ?array $uploadFiles, ?string $targetUrl = null): TurboStreamProcessorResult
    {
        $this->updateGardian->execute($payload);

        return new TurboStreamProcessorResult(true);
    }
}
