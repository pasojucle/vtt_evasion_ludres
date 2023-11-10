<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\UserDto;
use App\Repository\ParameterRepository;

class ParameterService
{
    public function __construct(
        private ParameterRepository $parameterRepository,
        private ReplaceKeywordsService $replaceKeywordsService
    ) {
    }

    public function getParameterByName(string $name)
    {
        $parameter = $this->parameterRepository->findOneByName($name);

        if ($parameter) {
            return $parameter->getValue();
        }

        return null;
    }

    public function getSchoolTestingRegistration(UserDto $user): array
    {
        $value = $this->getParameterByName('SCHOOL_TESTING_REGISTRATION');
        $message = $this->getParameterByName('SCHOOL_TESTING_REGISTRATION_MESSAGE');
        $message = $this->replaceKeywordsService->replace($user, $message);

        return [
            'value' => $value,
            'message' => $message,
        ];
    }
}
