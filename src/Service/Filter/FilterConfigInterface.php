<?php

declare(strict_types=1);

namespace App\Service\Filter;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

#[AutoconfigureTag('app.filter_config')]
interface FilterConfigInterface
{
    public function supports(string $route): bool;

    /** @return FilterFieldConfig[] */
    public function getFields(): array;

    /** @return FilterFieldConfig[] */
    public function getAdvancedFields(): array;
    
    public function getDataClass(): ?string;

    public function getEventSubscriber(): ?EventSubscriberInterface;
}
