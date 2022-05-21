<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\User;
use App\Repository\IndemnityRepository;
use App\Repository\MembershipFeeAmountRepository;
use App\Service\ClusterService;
use App\Service\IndemnityService;
use App\Service\ParameterService;
use App\Service\SeasonService;
use App\Twig\AppExtension;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class ServicesPresenter
{
    public ?array $seasonStartAt;
    public ?array $coverageFormStartAt;
    public string $productDirectory;
    public string $uploadsDirectory;
    public int $currentSeason;
    public array $seasonsStatus;
    public ?User $user;
    public array $allIndemnities;

    public function __construct(
        private SeasonService $seasonService,
        private ParameterService $parameterService,
        private ParameterBagInterface $parameterBag,
        private Security $security,
        public MembershipFeeAmountRepository $membershipFeeAmountRepository,
        public TranslatorInterface $translator,
        public AppExtension $appExtension,
        private IndemnityRepository $indemnityRepository,
        public IndemnityService $indemnityService,
        public ClusterService $clusterService
    ) {
        $this->productDirectory = $this->parameterBag->get('products_directory');
        $this->uploadsDirectory = $this->parameterBag->get('uploads_directory_path');
        $this->currentSeason = $this->seasonService->getCurrentSeason();
        $this->seasonsStatus = $this->seasonService->getSeasonsStatus();
        $this->user = $this->security->getUser();
        $this->seasonStartAt = $this->parameterService->getParameterByName('SEASON_START_AT');
        $this->coverageFormStartAt = $this->parameterService->getParameterByName('COVERAGE_FORM_AVAILABLE_AT');
        $this->allIndemnities = $this->indemnityRepository->findAll();
    }
}
