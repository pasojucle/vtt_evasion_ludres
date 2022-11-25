<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Entity\Level;
use App\Entity\Survey;
use App\Entity\User;
use App\Twig\AppExtension;
use DateInterval;
use DateTime;
use DateTimeImmutable;

class BikeRideViewModel extends AbstractViewModel
{
    public ?BikeRide $entity;

    public ?string $type;

    public ?string $title;

    public ?string $content;

    public ?DateTimeImmutable $startAt;

    public ?DateTimeImmutable $endAt;

    public ?int $displayDuration;

    public ?int $closingDuration;

    public ?bool $isWritableAvailability;

    public ?bool $isRegistrable;

    public ?int $minAge;

    public ?string $displayClass;

    public ?string $btnLabel;

    public ?string $period;

    public BikeRideType $bikeRideType;

    public ?Survey $survey;

    private ?DateTimeImmutable $today;

    private ?DateTimeImmutable $displayAt;

    private ?DateTimeImmutable $closingAt;

    private ServicesPresenter $services;

    public ?string $filename;

    public static function fromBikeRide(BikeRide $bikeRide, ServicesPresenter $services)
    {
        /** @var User $user */
        $user = $services->security->getUser();

        $bikeRideView = new self();
        $bikeRideView->entity = $bikeRide;
        $bikeRideView->bikeRideType = $bikeRide->getBikeRideType();
        $bikeRideView->title = $bikeRide->getTitle();
        $bikeRideView->type = $bikeRide->getBikeRideType()->getName();
        $bikeRideView->content = $bikeRide->getContent();
        $bikeRideView->startAt = $bikeRide->getStartAt();
        $bikeRideView->endAt = $bikeRide->getEndAt();
        $bikeRideView->closingDuration = $bikeRide->getClosingDuration();
        $bikeRideView->displayDuration = $bikeRide->getDisplayDuration();

        $today = new DateTimeImmutable();
        $bikeRideView->today = $today->setTime(0, 0, 0);
        $bikeRideView->displayAt = $bikeRideView->startAt->setTime(0, 0, 0);
        $bikeRideView->closingAt = $bikeRideView->startAt->setTime(23, 59, 59);
        $bikeRideView->displayClass = $bikeRideView->getDisplayClass();
        $bikeRideView->period = $bikeRideView->getPeriod($services->appExtension);
        $bikeRideView->isWritableAvailability = $services->isWritableAvailability->execute($bikeRide, $user);
        $bikeRideView->isRegistrable = $services->isRegistrable->execute($bikeRide, $user);
        $bikeRideView->btnLabel = $bikeRideView->getBtnLabel($user);
        $bikeRideView->survey = $bikeRide->getSurvey();

        $bikeRideView->services = $services;
        $bikeRideView->filename = $bikeRideView->getFilename();

        return $bikeRideView;
    }

    public function isOver(): bool
    {
        return $this->closingAt < $this->today;
    }

    public function isNext(): bool
    {
        $interval = new DateInterval('P' . $this->displayDuration . 'D');

        return $this->displayAt->sub($interval) <= $this->today && $this->today <= $this->closingAt;
    }

    private function getDisplayClass(): string
    {
        if ($this->isNext()) {
            return ' active';
        }
        if ($this->isOver()) {
            return ' disable';
        }

        return '';
    }

    private function getBtnLabel(?User $user): string
    {
        if ($this->isWritableAvailability) {
            return 'Disponibilité';
        }

        return 'S\'incrire';
    }

    private function getPeriod(AppExtension $appExtension): string
    {
        return  (null === $this->endAt)
            ? $appExtension->formatDateLong($this->startAt)
            : $appExtension->formatDateLong($this->startAt) . ' au ' . $appExtension->formatDateLong($this->endAt);
    }

    public function getClusters(): ClustersViewModel
    {
        return ClustersViewModel::fromClusters($this->entity->getClusters(), $this->services);
    }

    private function getFilename(): ?string
    {
        return ($this->entity->getFileName()) ? $this->services->uploadsDirectory . $this->entity->getFileName() : null;
    }
}
