<?php

declare(strict_types=1);

namespace App\Core\Contract\Filter;

use App\Core\Filter\FilterFieldConfig;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

#[AutoconfigureTag('app.filter_config')]
interface FilterConfigInterface
{
    public function getRouteName(): string;

    public function supports(string $route): bool;

    /** @return FilterFieldConfig[] */
    public function getFields(): array;

    /** @return FilterFieldConfig[] */
    public function getAdvancedFields(): array;
    
    public function getDataClass(): ?string;

    public function getEventSubscriber(): ?EventSubscriberInterface;

    public function isPaginated(): bool;
}
