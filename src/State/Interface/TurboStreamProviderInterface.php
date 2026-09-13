<?php

declare(strict_types=1);

namespace App\State\Interface;

use App\Dto\State\ViewContext;
use App\Dto\View\Interface\TurboStreamViewInterface;

/**
 * @template T of object
 */
interface TurboStreamProviderInterface extends FormComponentProviderInterface
{
    /**
     * @param T $entity
     */
    public function getStreamView(object $entity, ?ViewContext $context = null): TurboStreamViewInterface;
}
