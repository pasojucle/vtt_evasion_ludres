<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ParameterRepository;
use App\ViewModel\UserViewModel;

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

        $value = null;
        if ($parameter) {
            $value = $parameter->getValue();
        }

        return $value;
    }

    public function getSchoolTestingRegistration(UserViewModel $user): array
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
