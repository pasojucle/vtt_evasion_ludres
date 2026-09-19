<?php

declare(strict_types=1);

namespace App\State\Interface;

use App\Core\Contract\View\TurboStreamViewInterface;
use App\Dto\State\ViewContext;

/**
 * @template T of object
 */
interface TurboStreamProviderInterface
{
    /**
     * @param T $data
     */
    public function getStreamView(object $data, ?ViewContext $context = null): TurboStreamViewInterface;
}
