<?php

declare(strict_types=1);

namespace App\Service;

use DateTimeImmutable;
use App\ViewModel\UserViewModel;
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

        $value = null;
        if ($parameter) {
            $value = $parameter->getValue();
        }

        return $value;
    }

    public function getParameterStartAtByName(string $name): DateTimeImmutable
    {
        $today = new DateTimeImmutable();

        $seasonStartAtParam = $this->getParameterByName($name);
        $seasonStartAtParam['Y'] = $today->format('Y');
        $seasonStartAt = DateTimeImmutable::createFromFormat('d-m-Y', implode('-',$seasonStartAtParam));
        return $seasonStartAt->setTime(0,0,0);
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
