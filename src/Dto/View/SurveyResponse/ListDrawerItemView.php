<?php

declare(strict_types=1);

namespace App\Dto\View\SurveyResponse;

use App\Core\Contract\View\ComponentViewInterface;

readonly class ListDrawerItemView implements ComponentViewInterface
{
    public function __construct(
        public ?string $response,
        public ?string $fullName,
        public bool $isResponseText,
    ) {
    }

    public function getName(): string
    {
        return 'view';
    }

    public function getTemplate(): string
    {
        return 'survey_response/admin/_drawer_items.html.twig';
    }
}
