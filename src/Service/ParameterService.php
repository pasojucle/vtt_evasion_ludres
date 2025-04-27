<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Parameter;
use App\Repository\ParameterRepository;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

class ParameterService
{
    public function __construct(
        private ParameterRepository $parameterRepository,
        private MessageService $messageService,
        private ReplaceKeywordsService $replaceKeywordsService,
        private readonly RequestStack $request,
    ) {
    }

    public function getParameterByName(string $name): string|bool|array|int|null
    {
        try {
            $session = $this->request->getCurrentRequest()->getSession();
            $value = $session->get($name);
        } catch (SessionNotFoundException) {
            $session = null;
            $value = null;
        }

        if (null === $value) {
            $parameter = $this->parameterRepository->findOneByName($name);
            if ($parameter) {
                $value = $this->replaceKeywordsService->replaceCurrentSaison($parameter->getValue());
            }
            if ($session && $value) {
                $session->set($name, $value);
            }
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
