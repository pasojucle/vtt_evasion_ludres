<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\User;
use App\Repository\IndemnityRepository;
use App\Repository\LevelRepository;
use App\Repository\LicenceRepository;
use App\Repository\MembershipFeeAmountRepository;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Service\ClusterService;
use App\Service\IdentityService;
use App\Service\IndemnityService;
use App\Service\ParameterService;
use App\Service\ReplaceKeywordsService;
use App\Service\SeasonService;
use App\Service\UserService;
use App\Twig\AppExtension;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class ServicesPresenter
{
    public ?array $seasonStartAt;
    public ?array $coverageFormStartAt;
    public string $productDirectory;
    public string $uploadsDirectory;
    public string $uploadsDirectoryPath;
    public string $backgroundsDirectory;
    public string $backgroundsDirectoryPath;
    public int $currentSeason;
    public array $seasonsStatus;
    public ?User $user;
    public array $allIndemnities;
    public string $modalWindowOrderInProgress;

    public function __construct(
        private SeasonService $seasonService,
        private ParameterService $parameterService,
        private ParameterBagInterface $parameterBag,
        public Security $security,
        public MembershipFeeAmountRepository $membershipFeeAmountRepository,
        public TranslatorInterface $translator,
        public AppExtension $appExtension,
        private IndemnityRepository $indemnityRepository,
        public IndemnityService $indemnityService,
        public ClusterService $clusterService,
        public IdentityService $identityService,
        public UrlGeneratorInterface $router,
        public FormFactoryInterface $formFactory,
        public RequestStack $requestStack,
        public EntityManagerInterface $entityManager,
        public UserRepository $userRepository,
        public LicenceRepository $licenceRepository,
        public LevelRepository $levelRepository,
        // private UserService $userService,
        public SessionRepository $sessionRepository,
        public ReplaceKeywordsService $replaceKeywordsService
    ) {
        $this->productDirectory = $this->parameterBag->get('products_directory');
        $this->uploadsDirectoryPath = $this->parameterBag->get('uploads_directory_path');
        $this->backgroundsDirectory = $this->parameterBag->get('backgrounds_directory');
        $this->backgroundsDirectoryPath = $this->parameterBag->get('backgrounds_directory_path');
        $this->uploadsDirectory = $this->parameterBag->get('uploads_directory');
        $this->currentSeason = $this->seasonService->getCurrentSeason();
        $this->seasonsStatus = $this->seasonService->getSeasonsStatus();
        $this->seasonStartAt = $this->parameterService->getParameterByName('SEASON_START_AT');
        $this->coverageFormStartAt = $this->parameterService->getParameterByName('COVERAGE_FORM_AVAILABLE_AT');
        $this->modalWindowOrderInProgress = $this->parameterService->getParameterByName('MODAL_WINDOW_ORDER_IN_PROGRESS');
        $this->allIndemnities = $this->indemnityRepository->findAll();
    }
}
