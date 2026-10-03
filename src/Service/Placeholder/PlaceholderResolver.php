<?php

declare(strict_types=1);

namespace App\Service\Placeholder;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

readonly class PlaceholderResolver
{
    /**
     * @param iterable<PlaceholderResolverInterface> $resolvers
     */
    public function __construct(
        #[TaggedIterator('app.placeholder_resolver')]
        private iterable $resolvers,
    ) {
    }

    public function resolve(string $template, ?object ...$entities): string
    {
        $result = $template;
        $targets = [
            ...array_filter($entities, static fn (?object $e) => $e !== null),
            null
        ];

        foreach ($targets as $target) {
            foreach ($this->resolvers as $resolver) {
                if ($resolver->supports($target)) {
                    $result = $resolver->resolve($result, $target);
                }
            }
        }

        return $result;
    }
}
