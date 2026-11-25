<?php

declare(strict_types=1);

namespace App\State\Certificate\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\StreamDownloadProcessorResult;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class MemberCertificateCreateProcessor implements RedirectProcessorInterface
{
    public function process(PayloadInterface $payload, ?HandlerContext $context = null): StreamDownloadProcessorResult
    {
        return new StreamDownloadProcessorResult(
            $payload->data,
        );
    }
}
