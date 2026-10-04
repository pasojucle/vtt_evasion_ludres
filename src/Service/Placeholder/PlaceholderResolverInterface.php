<?php

declare(strict_types=1);

namespace App\Service\Placeholder;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.placeholder_resolver')]

interface PlaceholderResolverInterface
{
    public function supports(?object $data): bool;
    
    /**
     * Remplace les placeholders d'un texte par les valeurs de l'entité.
     */
    public function resolve(string $template, ?object $data = null): string;
}
