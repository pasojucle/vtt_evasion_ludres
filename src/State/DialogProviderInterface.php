<?php

declare(strict_types=1);

namespace App\State;

use App\Dto\DialogModalView;

/**
 * @template T of object
 */
interface DialogProviderInterface
{
    /**
     * @param T $entity
     */
    public function mapToView(object $entity): DialogModalView;
}