<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Dto\View\Interface\ComponentViewInterface;

readonly class TabWrapperView implements ComponentViewInterface
{
    /**
     * @param TabView[] $tabs
     */
    public function __construct(
        public string $name,
        public string $title,
        public string $description,
        public array $tabs,
        public TabEntityInterface $entity,
        public ?LinkView $fallback,
    ) {
    }

    public function getName(): string
    {
        return 'tabs';
    }

    public function getTemplate(): string
    {
        return 'components/_tabs_wrapper.html.twig';
    }
}
