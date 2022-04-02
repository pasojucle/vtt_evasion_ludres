<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Repository\MembershipFeeAmountRepository;
use App\Service\LicenceService;
use App\Twig\AppExtension;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class ServicesPresenter
{
    public function __construct(
        private LicenceService $licenceService,
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
