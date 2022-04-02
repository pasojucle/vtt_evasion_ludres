<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Twig\AppExtension;
use App\Service\LicenceService;
use App\Service\ParameterService;
use Symfony\Component\Security\Core\Security;
use App\Repository\MembershipFeeAmountRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ServicesPresenter
{
    public function __construct(
        private LicenceService $licenceService,
        private ParameterService $parameterService,
        private ParameterBagInterface $parameterBag,
        private Security $security,
        public MembershipFeeAmountRepository $membershipFeeAmountRepository,
        public TranslatorInterface $translator,
        public AppExtension $appExtension
    ) {
        $this->productDirectory = $this->parameterBag->get('products_directory');
        $this->uploadsDirectory = $this->parameterBag->get('uploads_directory_path');
        $this->currentSeason = $this->licenceService->getCurrentSeason();
        $this->seasonsStatus = $this->licenceService->getSeasonsStatus();
        $this->user = $this->security->getUser();
    }
}
