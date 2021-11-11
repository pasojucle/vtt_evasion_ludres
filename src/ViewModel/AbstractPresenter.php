<?php

namespace App\ViewModel;

use ReflectionClass;
use App\Service\LicenceService;
use Symfony\Component\Security\Core\Security;
use App\Repository\MembershipFeeAmountRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AbstractPresenter 
{
    private LicenceService $licenceService;
    private ParameterBagInterface $parameterBag;
    private Security $security;
    private MembershipFeeAmountRepository $membershipFeeAmountRepository;
    private TranslatorInterface $translator;
    private $viewModel;
    public array $service;

    public function __construct(
        LicenceService $licenceService, 
        ParameterBagInterface $parameterBag,
        Security $security,
        MembershipFeeAmountRepository $membershipFeeAmountRepository,
        TranslatorInterface $translator
    )
    {
        $this->licenceService = $licenceService;
        $this->parameterBag = $parameterBag;
        $this->security = $security;
        $this->membershipFeeAmountRepository = $membershipFeeAmountRepository;
        $this->translator = $translator;
        $this->services = $this->getServices();
    }

    private function getServices(): array
    {
        return [
            'productDirectory' => $this->parameterBag->get('products_directory'),
            'currentSeason' => $this->licenceService->getCurrentSeason(),
            'seasonsStatus' => $this->licenceService->getSeasonsStatus(),
            'user' => $this->security->getUser(),
            'membershipFeeAmountRepository' => $this->membershipFeeAmountRepository,
            'translator' => $this->translator,
        ];
    }

}