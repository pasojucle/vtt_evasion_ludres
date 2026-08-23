<?php

declare(strict_types=1);

namespace App\State\Interface;

use App\Dto\State\TurboStreamContext;
use App\Dto\View\Interface\TurboStreamViewInterface;

/**
 * @template T of object
 */
interface TurboStreamProviderInterface extends FormComponentProviderInterface
{
    /**
     * @param T $entity
     */
    public function getStreamView(object $entity, ?TurboStreamContext $context = null): TurboStreamViewInterface;
}
