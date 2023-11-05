<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\LicenceDto;
use App\Entity\Licence;
use App\Entity\SwornCertification;
use App\Entity\User;
use App\Model\Currency;
use App\Repository\MembershipFeeAmountRepository;
use App\Repository\RegistrationChangeRepository;
use App\Service\IndemnityService;
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
        private RegistrationChangeRepository $registrationChangeRepository,
        private TranslatorInterface $translator,
        private ParameterService $parameterService,
        private MembershipFeeAmountRepository $membershipFeeAmountRepository,
        private IndemnityService $indemnityService,
        private ProjectDirService $projectDir,
    ) {
        $this->seasonsStatus = $this->seasonService->getSeasonsStatus();
    }

    public function fromEntity(?Licence $licence, ?array $changes = null): LicenceDto
    {
        $licenceDto = new LicenceDto();

        if ($licence) {
            $currentSeason = $this->seasonService->getCurrentSeason();
            
            $licenceDto->id = $licence->getId();
            $licenceDto->createdAt = ($licence->getCreatedAt()) ? $licence->getCreatedAt()->format('d/m/Y') : null;
            $licenceDto->season = $this->getSeason($licence->getSeason());
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
            $licenceDto->amount = $this->getAmount($licence);
            $licenceDto->registrationTitle = $this->getRegistrationTitle($licence);
            $licenceDto->swornCertifications = $this->getSwornCertifications($licence);

            if ($changes) {
                $this->formatChanges($changes, $licenceDto);
            }
        }

        return $licenceDto;
    }

    public function fromEntities(Collection|array $licenceEntities, ?array $changes = null): array
    {
        $licences = [];
        foreach ($licenceEntities as $licenceEntity) {
            $licences[] = $this->fromEntity($licenceEntity, $changes);
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

    public function getCurrentSeasonForm(Licence $licence, int $currentSeason)
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

    private function formatChanges(array $changes, LicenceDto &$licenceDto): void
    {
        if (array_key_exists('Licence', $changes) && array_key_exists($licenceDto->id, $changes['Licence'])) {
            $properties = array_keys($changes['Licence'][$licenceDto->id]->getValue());
            foreach ($properties as $property) {
                if ('coverage' === $property) {
                    $property = 'coverageStr';
                }
                if ('status' === $property) {
                    continue;
                }

                $licenceDto->$property = sprintf('<b>%s</b>', $licenceDto->$property);
            }
        }
    }

    private function getAmount(Licence $licence): array
    {
        $amount = null;
        $amountStr = '';
        $indemnities = null;

        if ($licence->isFinal()) {
            $isNewMember = $this->isNewMember($licence->getUser());
            $membershipFee = (null !== $licence->getCoverage() && null !== $licence->getAdditionalFamilyMember())
                ? $this->membershipFeeAmountRepository->findOneByLicence($licence->getCoverage(), $isNewMember, $licence->getAdditionalFamilyMember())
                : null;
            if (null !== $membershipFee) {
                $amount = $membershipFee->getAmount();
            }
            $indemnities = $this->indemnityService->getUserIndemnities($licence->getUser(), $licence->getSeason() - 1);

            if (null !== $amount && null !== $indemnities) {
                $amount -= $indemnities->getAmount();
            }

            if (null !== $amount) {
                $amount = new Currency($amount);
                $coverageSrt = $this->translator->trans(Licence::COVERAGES[$licence->getCoverage()]);
                $amountStr = "Le montant de votre inscription pour la formule d'assurance {$coverageSrt} est de {$amount->toString()}";
            }
        } else {
            $amountStr = "Votre inscription aux trois séances consécutives d'essai est gratuite.<br>Votre assurance gratuite est garantie sur la formule Mini-braquet.";
        }

        return [
            'value' => $amount,
            'str' => $amountStr,
            'indemnities' => $indemnities,
        ];
    }

    private function isNewMember(User $user): bool
    {
        return 2 > $user->getLicences()->count();
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

    private function getSwornCertifications(Licence $licence): string
    {
        $swornCertifications = '';
        /** @var SwornCertification  $swornCertification */
        foreach ($licence->getSwornCertifications() as $swornCertification) {
            $checkImg = base64_encode(file_get_contents($this->projectDir->path('logos', 'check-square-regular.png')));
            $swornCertifications .= sprintf('<p><img src="data:image/png;base64, %s"/> %s</p>', $checkImg, $swornCertification->getLabel());
        }
        return $swornCertifications;
    }
}
