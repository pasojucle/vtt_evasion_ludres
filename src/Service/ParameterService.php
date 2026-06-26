<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Parameter;
use App\Repository\ParameterRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class ParameterService
{
    public function __construct(
        private ParameterRepository $parameterRepository,
        private MessageService $messageService,
        private ReplaceKeywordsService $replaceKeywordsService,
        private RequestStack $requestStack,
    ) {
    }

    public function getParameterById(string $id): string|bool|array|int|null
    {
        $request = $this->requestStack->getCurrentRequest();
        $session = ($request && $request->hasSession()) ? $request->getSession() : null;

        if ($session && $session->has($id)) {
            return $session->get($id);
        }

        $parameter = $this->parameterRepository->findOneById($id);
        if (!$parameter) {
            return null;
        }

        $value = $parameter->getValue();

        if ($session) {
            $session->set($id, $value);
        }

        return $value;
    }

    public function getParametersByParameterGroupName(string $name): array
    {
        $parameters = [];
        
        /** @var Parameter $parameter */
        foreach ($this->parameterRepository->findBySectionId($name) as $parameter) {
            $parameters[] = [
                'id' => $parameter->getId(),
                'label' => $this->replaceKeywordsService->replaceCurrentSaison($parameter->getLabel()),
            ];
        };
        return $parameters;
    }

    public function getSchoolTestingRegistration(): array
    {
        $value = $this->getParameterById('SCHOOL_TESTING_REGISTRATION');
        $message = $this->messageService->getMessageById('SCHOOL_TESTING_REGISTRATION_MESSAGE');
        $message = $this->replaceKeywordsService->replace($message);

        return [
            'value' => $value,
            'message' => $message,
        ];
    }
}
