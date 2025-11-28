<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\LicenceDto;
use App\Entity\Enum\LicenceStateEnum;
use App\Entity\Level;
use App\Entity\Licence;
use App\Entity\LicenceConsent;
use App\Entity\User;
use App\Model\Currency;
use App\Repository\HistoryRepository;
use App\Repository\LicenceRepository;
use App\Repository\MembershipFeeAmountRepository;
use App\Service\IndemnityService;
use App\Service\LicenceService;
use App\Service\ParameterService;
use App\Service\ProjectDirService;
use App\Service\SeasonService;
use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Symfony\Contracts\Translation\TranslatorInterface;

class LicenceDtoTransformer
{
    public function __construct(
        private SeasonService $seasonService,
        private HistoryRepository $historyRepository,
        private TranslatorInterface $translator,
        private ParameterService $parameterService,
        private MembershipFeeAmountRepository $membershipFeeAmountRepository,
        private IndemnityService $indemnityService,
        private ProjectDirService $projectDir,
        private readonly LicenceService $licenceService,
        private readonly LicenceRepository $licenceRepository,
        private readonly LicenceAuthorizationDtoTransformer $licenceAuthorizationDtoTransformer
    ) {
    }

    public function fromEntity(?Licence $licence, ?array $histories = null): LicenceDto
    {
        $licenceDto = new LicenceDto();

        if ($licence) {
            $currentSeason = $this->seasonService->getCurrentSeason();
            
            $licenceDto->id = $licence->getId();
            $licenceDto->createdAt = $licence->getCreatedAt()?->format('d/m/Y');
            $licenceDto->createdAtLong = $licence->getCreatedAt()?->format('d/m/Y H.i');
            $licenceDto->testingAt = $licence->getTestingAt()?->format('d/m/Y');
            $licenceDto->testingAtLong = $licence->getTestingAt()?->format('d/m/Y H.i');
            $licenceDto->season = $licence->getSeason();
            $licenceDto->shortSeason = $this->getSeason($licence->getSeason());
            $licenceDto->fullSeason = $this->getFullSeason($licence->getSeason());
            $licenceDto->isYearly = $licence->getState()->isYearly();
            $licenceDto->coverage = (null !== $licence->getCoverage()) ? $licence->getCoverage() : null;
            $licenceDto->coverageStr = (!empty($licence->getCoverage())) ? $this->translator->trans(Licence::COVERAGES[$licence->getCoverage()]) : null;
            $licenceDto->options = $licence->getOptions();
            $licenceDto->hasFamilyMember = $licence->getAdditionalFamilyMember();
            $licenceDto->category = $licence->getCategory();
            $licenceDto->state = $this->getState($licence->getState());
            $licenceDto->lock = $licence->getSeason() !== $currentSeason;
            $licenceDto->currentSeasonForm = $this->getCurrentSeasonForm($licence, $currentSeason);
            $licenceDto->isVae = $this->isVae($licence->isVae());
            $licenceDto->toValidate = $licence->getState()->toValidate();
            $licenceDto->toRegister = $licence->getState()->toRegister();
            $licenceDto->isRegistered = $licence->getState()->isRegistered();
            $licenceDto->isSeasonLicence = $licence->getSeason() === $currentSeason;
            $licenceDto->amount = $this->getAmount($licence, $currentSeason);
            $licenceDto->registrationTitle = $this->getRegistrationTitle($licence);
            $licenceDto->licenceAuthorizationConsents = $this->getLicenceAuthorizationConsents($licence);
            $licenceDto->licenceHealthConsents = $licence->getLicenceHealthConsents();
            $licenceDto->licenceOvewiewConsents = $licence->getLicenceOverviewConsents();
            $licenceDto->authorizations = $this->licenceAuthorizationDtoTransformer->fromEntities($licence->getLicenceAuthorizations());
            $licenceDto->isActive = $this->licenceService->isActive($licence);
            if ($licence->getAdditionalFamilyMember()) {
                $licenceDto->additionalFamilyMember = 'Un membre de votre famille est déjà inscrit au club (remise de 10€ incluse)';
            }
            if ($histories) {
                $this->getDecoratedChanges($histories, $licenceDto);
            }
        }

        return $licenceDto;
    }

    public function fromEntities(Collection|array $licenceEntities, ?array $histories = null): array
    {
        $licences = [];
        foreach ($licenceEntities as $licenceEntity) {
            $licences[] = $this->fromEntity($licenceEntity, $histories);
        }

        return $licences;
    }

    public function getRegistrationTitle(Licence $licence): string
    {
        $title = $this->translator->trans('registration_step.type.default');
        $title = 'registration_step.type.';

        if (null !== $this) {
            if (false === $licence->getState()->isYearly()) {
                $title .= 'testing';
            } else {
                if (null !== $licence->getCategory()) {
                    $title .= $licence->getCategory()->value;
                }
            }
        }

        return $this->translator->trans($title);
    }

    public function getCurrentSeasonForm(Licence $licence, int $currentSeason): bool
    {
        $coverageFormStartAt = $this->parameterService->getParameterByName('COVERAGE_FORM_AVAILABLE_AT');
        $seasonStartAt = $this->parameterService->getParameterByName('SEASON_START_AT');
        $coverageFormStartAt['year'] = ($seasonStartAt['month'] <= $coverageFormStartAt['month']
            && $seasonStartAt['day'] <= $coverageFormStartAt['day'])
            ? $currentSeason - 1
            : $currentSeason;

        $coverageFormStartAt = new DateTimeImmutable(implode('-', array_reverse($coverageFormStartAt)));

        return $coverageFormStartAt->setTime(0, 0, 0) < new DateTime() && !$licence->getCurrentSeasonForm();
    }

    private function isVae(bool $isVae): string
    {
        return $isVae ? 'VTT à assistance électrique' : '';
    }

    private function getSeason(int $season): string
    {
        return sprintf('%s - %s', (string) ($season - 1), (string) $season);
    }

    private function getFullSeason(int $season): string
    {
        return sprintf('%s - %s (jusqu\'au 31 décembre %s) ', (string) ($season - 1), (string) $season, (string) $season);
    }

    private function getDecoratedChanges(array $histories, LicenceDto &$licenceDto): void
    {
        if (array_key_exists('Licence', $histories) && array_key_exists($licenceDto->id, $histories['Licence'])) {
            $properties = array_keys($histories['Licence'][$licenceDto->id]->getValue());
            foreach ($properties as $property) {
                if ('coverage' === $property) {
                    $property = 'coverageStr';
                }
                if ('status' === $property) {
                    continue;
                }
                if (is_string($licenceDto->$property)) {
                    $licenceDto->$property = sprintf('<ins style="background-color:#ccffcc">%s</ins>', $licenceDto->$property);
                }
            }
        }
    }

    private function getAmount(Licence $licence, $currentSeason): array
    {
        $membershipFeeAmount = 0;
        $amount = null;
        $amountToStr = '';

        if ($licence->getState()->isYearly()) {
            /** @var User $user */
            $user = $licence->getUser();
            $isNewMember = $this->isNewMember($user, $currentSeason);
            $membershipFee = (null !== $licence->getCoverage() && null !== $licence->getAdditionalFamilyMember())
                ? $this->membershipFeeAmountRepository->findOneByLicence($licence->getCoverage(), $isNewMember, $licence->getAdditionalFamilyMember())
                : null;
            if (null !== $membershipFee) {
                $membershipFeeAmount = $membershipFee->getAmount();
            }
            $lastSeason = $licence->getSeason() - 1;
            $indemnities = $this->indemnityService->getUserIndemnities($licence->getUser(), $this->seasonService->getSeasonPeriod($lastSeason));

            if ($membershipFeeAmount) {
                $amount = $membershipFeeAmount;
            }

            if ($amount) {
                $amount = new Currency($amount);
                $amount->sub($indemnities);

                $coveragesToString = $this->translator->trans(Licence::COVERAGES[$licence->getCoverage()]);
                if ($user->getLevel()->getType() === Level::TYPE_FRAME) {
                    $amountToStr .= sprintf('Le montent des indemnités pour votre participation active à la vie du club durant la saison %s est de %s<br>', $lastSeason, $indemnities->toString())
                                . sprintf('Tarif de la licence : %s<br>', (new Currency($membershipFeeAmount))->toString());
                }
                if ($licence->getAdditionalFamilyMember()) {
                    $amountToStr .= "Un membre de votre famille est déja inscrit au club (remise de 10€ incluse)</br>";
                }
                $amountToStr .= "<b>Le montant de votre inscription et de la formule d'assurance {$coveragesToString} est de <span class=\"licence-amount\">{$amount->toString()}</span></b>";
            }
        } else {
            $amountToStr = "Votre inscription aux trois séances consécutives d'essai est gratuite.<br>Votre assurance gratuite est garantie sur la formule Mini-braquet.";
        }

        return [
            'value' => $amount,
            'str' => $amountToStr,
        ];
    }

    private function isNewMember(User $user, $currentSeason): bool
    {
        $previousLicence = $this->licenceRepository->findOneByUserAndLastSeason($user);

        return !$previousLicence || $currentSeason - 1 !== $previousLicence->getSeason() || !$previousLicence->getState()->isYearly() || Licence::FILTER_VALID > $previousLicence->getState();
    }

    private function getState(LicenceStateEnum $state): array
    {
        return [
            'value' => $state,
            'label' => $state->trans($this->translator),
            'className' => $state->getClassName(),
        ];
    }

    private function getLicenceAuthorizationConsents(Licence $licence): array
    {
        $licenceAutorizationConsents = [];
        /** @var LicenceConsent  $licenceAuthorizationConsent */
        foreach ($licence->getLicenceAuthorizationConsents() as $licenceAuthorizationConsent) {
            $licenceAutorizationConsents[$licenceAuthorizationConsent->getConsent()->getId()] = $licenceAuthorizationConsent;
        }
        return $licenceAutorizationConsents;
    }
}
