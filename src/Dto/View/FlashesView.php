<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Dto\View\Interface\TurboStreamViewInterface;

readonly class FlashesView implements TurboStreamViewInterface
{
    /**
    * @param array<string, list<string>> $flashes     */
    public function __construct(
        public array $flashes,
    ) {
    }

    public static function create(string $type, string $message): self
    {
        return new self([
            $type => [$message],
        ]);
    }

    public function getStreamTemplate(): string
    {
        return 'flash_messages/flash_messages.lazy.html.twig';
    }
}
