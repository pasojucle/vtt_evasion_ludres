<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Core\Contract\View\ComponentViewInterface;
use App\Core\Contract\View\TabWrapperHeaderInterface;

readonly class TabWrapperView implements ComponentViewInterface
{
    /**
     * @param TabView[] $tabs
     */
    public function __construct(
        public string $name,
        public string $title,
        public TabWrapperHeaderInterface $header,
        public array $tabs,
        public ?LinkView $fallback,
    ) {
    }

    public function getName(): string
    {
        return 'tabs';
    }

    public function getTemplate(): string
    {
        return 'components/tab/_tabs_wrapper.html.twig';
    }
}
