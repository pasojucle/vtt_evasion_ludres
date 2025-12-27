<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Parameter;
use App\Repository\ParameterRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class ParameterService
{
    private array $arrayParameterCache = [];

    public function __construct(
        private ParameterRepository $parameterRepository,
        private MessageService $messageService,
        private ReplaceKeywordsService $replaceKeywordsService,
        private RequestStack $requestStack,
    ) {
    }

    public function getParameterByName(string $name): string|bool|array|int|null
    {
        $request = $this->requestStack->getCurrentRequest();
        $session = ($request && $request->hasSession()) ? $request->getSession() : null;

        if ($session && $session->has($name)) {
            return $session->get($name);
        }

        $parameter = $this->parameterRepository->findOneByName($name);
        if (!$parameter) {
            return null;
        }

        $value = $parameter->getValue();

        if ($session) {
            $session->set($name, $value);
        }

        return $value;
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

    public function getSchoolTestingRegistration(): array
    {
        $value = $this->getParameterByName('SCHOOL_TESTING_REGISTRATION');
        $message = $this->messageService->getMessageByName('SCHOOL_TESTING_REGISTRATION_MESSAGE');
        $message = $this->replaceKeywordsService->replace($message);

        return [
            'value' => $value,
            'message' => $message,
        ];
    }
}
