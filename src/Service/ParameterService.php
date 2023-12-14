<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\UserDto;
use App\Entity\Parameter;
use App\Repository\ParameterRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class ParameterService
{
    public function __construct(
        private ParameterRepository $parameterRepository,
        private ReplaceKeywordsService $replaceKeywordsService,
        private RequestStack $requestStack,
    ) {
    }

    public function getParameterByName(string $name): string|bool|array|int|null
    {
        $parameter = $this->parameterRepository->findOneByName($name);

        if ($parameter) {
            return $this->getReplaceKeywords($parameter->getValue());
        }

        return null;
    }
    public function getParametesrByParameterGroupName(string $name): array
    {
        $parameters = [];
        
        /** @var Parameter $parameter */
        foreach ($this->parameterRepository->findByParameterGroupName($name) as $parameter) {
            $parameters[] = [
                'name' => $parameter->getName(),
                'label' => $this->getReplaceKeywords($parameter->getLabel()),
            ];
        };
        return $parameters;
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

    public function getReplaceKeywords(string|bool|array|int|null $content): string|bool|array|int|null
    {
        if (is_string($content)) {
            $session = $this->requestStack->getSession();
            return str_replace('{{ saison_actuelle }}', (string) $session->get('currentSeason'), $content);
        }

        return $content;
    }
}
