<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\UserDto;
use App\Entity\Parameter;
use App\Repository\ParameterRepository;

class ParameterService
{
    public function __construct(
        private ParameterRepository $parameterRepository,
        private MessageService $messageService,
        private ReplaceKeywordsService $replaceKeywordsService,
    ) {
    }

    public function getParameterByName(string $name): string|bool|array|int|null
    {
        $parameter = $this->parameterRepository->findOneByName($name);

        if ($parameter) {
            return $this->replaceKeywordsService->replaceCurrentSaison($parameter->getValue());
        }

        return null;
    }

    public function getParametersByParameterGroupName(string $name): array
    {
        $parameters = [];
        
        /** @var Parameter $parameter */
        foreach ($this->parameterRepository->findByParameterGroupName($name) as $parameter) {
            $parameters[] = [
                'name' => $parameter->getName(),
                'label' => $this->replaceKeywordsService->replaceCurrentSaison($parameter->getLabel()),
            ];
        };
        return $parameters;
    }

    public function getSchoolTestingRegistration(UserDto $user): array
    {
        $value = $this->getParameterByName('SCHOOL_TESTING_REGISTRATION');
        $message = $this->messageService->getMessageByName('SCHOOL_TESTING_REGISTRATION_MESSAGE');
        $message = $this->replaceKeywordsService->replace($user, $message);

        return [
            'value' => $value,
            'message' => $message,
        ];
    }
}
