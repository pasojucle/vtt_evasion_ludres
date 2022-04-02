<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\Licence;
use DateTime;
use DateTimeImmutable;

class LicenceViewModel extends AbstractViewModel
{
    private const STATUS_CLASS = [
        Licence::STATUS_NONE => 'alert-black',
        Licence::STATUS_WAITING_RENEW => 'alert-danger',
        Licence::STATUS_IN_PROCESSING => 'alert-warning',
        Licence::STATUS_WAITING_VALIDATE => 'alert-warning',
        Licence::STATUS_TESTING => 'success-test',
        Licence::STATUS_VALID => 'success',
    ];
    public ?Licence $entity;

    public ?string $createdAt;

    public ?int $season;

    public ?bool $isFinal;

    public ?int $coverage;

    public ?string $coverageStr;

    public ?bool $hasFamilyMember;

    public ?int $category;

    public ?string $statusClass;

    public ?int $status;

    public ?string $statusStr;

    public ?string $type;

    public ?bool $lock;

    public ?string $amount;

    public ?string $amountStr;

    public bool $currentSeasonForm = false;

    private ServicesPresenter $services;

    public static function fromLicence(?Licence $licence, bool $isNewMember, ServicesPresenter $services)
    {
        $licenceView = new self();
        if ($licence) {
            $status = $licence->getStatus();
            if ($licence->getSeason() !== $services->currentSeason) {
                if ($services->seasonsStatus[Licence::STATUS_NONE] >= $licence->getSeason()) {
                    $status = Licence::STATUS_NONE;
                }
                if ($services->seasonsStatus[Licence::STATUS_WAITING_RENEW] === $licence->getSeason()) {
                    $status = Licence::STATUS_WAITING_RENEW;
                }
            }
            $licenceView->entity = $licence;
            $licenceView->services = $services;
            $licenceView->createdAt = ($licence->getCreatedAt()) ? $licence->getCreatedAt()->format('d/m/Y') : null;
            $licenceView->season = $licence->getSeason();
            $licenceView->isFinal = $licence->isFinal();
            $licenceView->coverage = (null !== $licence->getCoverage()) ? $licence->getCoverage() : null;
            $licenceView->coverageStr = (!empty($licence->getCoverage())) ? Licence::COVERAGES[$licence->getCoverage()] : null;
            $licenceView->hasFamilyMember = $licence->getAdditionalFamilyMember();
            $licenceView->category = $licence->getCategory();
            $licenceView->statusClass = self:: STATUS_CLASS[$status];
            $licenceView->status = $status;
            $licenceView->statusStr = Licence::STATUS[$status];
            $licenceView->type = (!empty($licence->getType())) ? Licence::TYPES[$licence->getType()] : null;
            $licenceView->lock = $licence->getSeason() !== $services->currentSeason;
            $licenceView->currentSeasonForm = $licenceView->getCurrentSeasonForm();

            $licenceView->amount = $licenceView->getAmount($isNewMember)['value'].' €';
            $licenceView->amountStr = $licenceView->getAmount($isNewMember)['str'];
        }

        return $licenceView;
    }

    private function getAmount(bool $isNewMember): array
    {
        $amount = null;
        $amountStr = '';

        if ($this->isFinal) {
            $membershipFee = (null !== $this->coverage && null !== $this->hasFamilyMember && null !== $isNewMember)
                ? $this->services->membershipFeeAmountRepository->findOneByLicence($this->coverage, $isNewMember, $this->hasFamilyMember)
                : null;
            if (null !== $membershipFee) {
                $amount = $membershipFee->getAmount();
            }
            if (null !== $amount) {
                $coverageSrt = $this->services->translator->trans(Licence::COVERAGES[$this->coverage]);
                $amountStr = "Le montant de votre inscription pour la formule d'assurance {$coverageSrt} est de {$amount} €";
            }
        } else {
            $amountStr = "Votre inscription aux trois séances consécutives d'essai est gratuite.<br>Votre assurance gratuite est garantie sur la formule Mini-braquet.";
        }

        return [
            'value' => $amount,
            'str' => $amountStr,
        ];
    }

    public function getRegistrationTitle(): string
    {
        $title = $this->services->translator->trans('registration_step.type.default');
        $title = 'registration_step.type.';
        if (null !== $this) {
            if (!$this->isFinal) {
                $title .= 'testing';
            } else {
                if (null !== $this->category) {
                    $categories = [
                        Licence::CATEGORY_MINOR => 'minor',
                        Licence::CATEGORY_ADULT => 'adult',
                    ];
                    $title .= $categories[$this->category];
                }
            }
        }

        return $this->services->translator->trans($title);
    }

    public function getCurrentSeasonForm()
    {
        $coverageFormStartAt = $this->parameterService->getParameterStartAtByName('COVERAGE_FORM_AVAILABLE_AT');
        
        return $coverageFormStartAt < new DateTime() && !$this->entity->getCurrentSeasonForm();
    }
}
