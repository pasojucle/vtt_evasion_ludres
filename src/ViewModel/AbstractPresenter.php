<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Repository\MembershipFeeAmountRepository;
use App\Service\LicenceService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class AbstractPresenter
{
    public array $service;
    private LicenceService $licenceService;

    private ParameterBagInterface $parameterBag;

    private Security $security;

    private MembershipFeeAmountRepository $membershipFeeAmountRepository;

    private TranslatorInterface $translator;

    private $viewModel;

    public function __construct(
        LicenceService $licenceService,
        ParameterBagInterface $parameterBag,
        Security $security,
        MembershipFeeAmountRepository $membershipFeeAmountRepository,
        TranslatorInterface $translator
    ) {
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
