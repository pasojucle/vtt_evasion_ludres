<?php

declare(strict_types=1);

namespace App\State\Registration\Provider;

use App\Service\MessageService;
use App\Service\ParameterService;
use App\Service\ReplaceKeywordsService;

class SchoolRegistrationProvider
{
    public function __construct(
        private ParameterService $parameterService,
        private MessageService $messageService,
        private ReplaceKeywordsService $replaceKeywordsService,
    ) {
    }

    public function getSettings(): array
    {
        $value = $this->parameterService->getParameterById('SCHOOL_TESTING_REGISTRATION');
        $message = $this->messageService->getMessageById('SCHOOL_TESTING_REGISTRATION_MESSAGE');
        $message = $this->replaceKeywordsService->replace($message);

        return [
            'value' => $value,
            'message' => $message,
        ];
    }
}
