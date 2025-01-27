<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\LicenceDto;
use App\Entity\Level;
use App\Entity\Licence;
use App\Entity\LicenceSwornCertification;
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
    private const STATUS_CLASS = [
        Licence::STATUS_NONE => 'alert-black',
        Licence::STATUS_WAITING_RENEW => 'alert-danger',
        Licence::STATUS_IN_PROCESSING => 'alert-warning',
        Licence::STATUS_WAITING_VALIDATE => 'alert-warning',
        Licence::STATUS_TESTING => 'success-test',
        Licence::STATUS_VALID => 'success',
    ];

    private array $seasonsStatus;
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
    ) {
        $this->seasonsStatus = $this->seasonService->getSeasonsStatus();
    }

    public function fromEntity(?Licence $licence, ?array $histories = null): LicenceDto
    {
        $licenceDto = new LicenceDto();

        if ($licence) {
            $currentSeason = $this->seasonService->getCurrentSeason();
            
            $licenceDto->id = $licence->getId();
            $licenceDto->createdAt = ($licence->getCreatedAt()) ? $licence->getCreatedAt()->format('d/m/Y') : null;
            $licenceDto->createdAtLong = ($licence->getCreatedAt()) ? $licence->getCreatedAt()->format('d/m/Y H.i') : null;
            $licenceDto->season = $licence->getSeason();
            $licenceDto->shortSeason = $this->getSeason($licence->getSeason());
            $licenceDto->fullSeason = $this->getFullSeason($licence->getSeason());
            $licenceDto->isFinal = $licence->isFinal();
            $licenceDto->coverage = (null !== $licence->getCoverage()) ? $licence->getCoverage() : null;
            $licenceDto->coverageStr = (!empty($licence->getCoverage())) ? $this->translator->trans(Licence::COVERAGES[$licence->getCoverage()]) : null;
            $licenceDto->hasFamilyMember = $licence->getAdditionalFamilyMember();
            $licenceDto->category = $licence->getCategory();
            $licenceDto->status = $this->getStatus($licence, $currentSeason);
            $licenceDto->statusClass = self:: STATUS_CLASS[$licenceDto->status];
            $licenceDto->statusStr = Licence::STATUS[$licenceDto->status];
            $licenceDto->lock = $licence->getSeason() !== $currentSeason;
            $licenceDto->currentSeasonForm = $this->getCurrentSeasonForm($licence, $currentSeason);
            $licenceDto->isVae = $this->isVae($licence->isVae());
            $licenceDto->toValidate = $this->getToValidate($licence->getStatus());
            $licenceDto->isSeasonLicence = $licence->getSeason() === $currentSeason;
            $licenceDto->amount = $this->getAmount($licence, $currentSeason);
            $licenceDto->registrationTitle = $this->getRegistrationTitle($licence);
            $licenceDto->licenceSwornCertifications = $this->getLicenceSwornCertifications($licence);
            $licenceDto->isActive = $this->licenceService->isActive($licence);
            if ($licence->getAdditionalFamilyMember()) {
                $licenceDto->additionalFamilyMember = 'Un membre de votre famille est déjà inscrit au club';
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
            if (false === $licence->isFinal()) {
                $title .= 'testing';
            } else {
                if (null !== $licence->getCategory()) {
                    $categories = [
                        Licence::CATEGORY_MINOR => 'minor',
                        Licence::CATEGORY_ADULT => 'adult',
                    ];
                    $title .= $categories[$licence->getCategory()];
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

    private function getToValidate(int $status): bool
    {
        return in_array($status, [Licence::STATUS_WAITING_VALIDATE, Licence::STATUS_WAITING_RENEW]);
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

        if ($licence->isFinal()) {
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
                    $amountToStr .= "Un membre de votre famille est déja inscrit au club</br>";
                }
                $amountToStr .= "<b>Le montant de votre inscription pour la formule d'assurance {$coveragesToString} est de <span class=\"licence-amount\">{$amount->toString()}</span></b>";
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

        return !$previousLicence || $currentSeason - 1 !== $previousLicence->getSeason() || !$previousLicence->isFinal() || Licence::STATUS_VALID > $previousLicence->getStatus();
    }

    private function getStatus(Licence $licence, int $currentSeason): int
    {
        $status = $licence->getStatus();
        if ($licence->getSeason() !== $currentSeason) {
            if ($this->seasonsStatus[Licence::STATUS_NONE] >= $licence->getSeason()) {
                $status = Licence::STATUS_NONE;
            }
            if ($this->seasonsStatus[Licence::STATUS_WAITING_RENEW] === $licence->getSeason()) {
                $status = Licence::STATUS_WAITING_RENEW;
            }
        }
        return $status;
    }

    private function getLicenceSwornCertifications(Licence $licence): string
    {
        $licenceSwornCertifications = '';
        /** @var LicenceSwornCertification  $licenceSwornCertification */
        foreach ($licence->getLicenceSwornCertifications() as $licenceSwornCertification) {
            $checkImg = base64_encode(file_get_contents($this->projectDir->path('logos', 'check-square-regular.png')));
            $licenceSwornCertifications .= sprintf('<p><img src="data:image/png;base64, %s"/> %s</p>', $checkImg, $licenceSwornCertification->getSwornCertification()->getLabel());
        }
        return $licenceSwornCertifications;
    }
}
