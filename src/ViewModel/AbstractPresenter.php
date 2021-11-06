<?php

namespace App\ViewModel;

use ReflectionClass;
use App\Service\LicenceService;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AbstractPresenter 
{
    private LicenceService $licenceService;
    private ParameterBagInterface $parameterBag;
    private Security $security;
    private $viewModel;
    public array $data;

    public function __construct(LicenceService $licenceService, ParameterBagInterface $parameterBag,Security $security)
    {
        $this->licenceService = $licenceService;
        $this->parameterBag = $parameterBag;
        $this->security = $security;
        $this->data = $this->getData();
    }

    private function getData(): array
    {
        return [
            'productDirectory' => $this->parameterBag->get('products_directory'),
            'currentSeason' => $this->licenceService->getCurrentSeason(),
            'seasonsStatus' => $this->licenceService->getSeasonsStatus(),
            'user' => $this->security->getUser(),
        ];
    }

}