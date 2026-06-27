<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ParameterRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class ParameterService
{
    public function __construct(
        private ParameterRepository $parameterRepository,
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

    // public function getParametersByParameterGroupName(string $name): array
    // {
    //     $parameters = [];
        
    //     /** @var Parameter $parameter */
    //     foreach ($this->parameterRepository->findBySectionId($name) as $parameter) {
    //         $parameters[] = [
    //             'id' => $parameter->getId(),
    //             'label' => $this->replaceKeywordsService->replaceCurrentSaison($parameter->getLabel(), $this->seasonService->getCurrentSeason()),
    //         ];
    //     };
    //     return $parameters;
    // }
}
