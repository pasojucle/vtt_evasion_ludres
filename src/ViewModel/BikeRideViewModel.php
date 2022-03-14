<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\BikeRide;
use App\Entity\Level;
use App\Entity\User;
use App\Twig\AppExtension;
use DateInterval;
use DateTime;
use DateTimeImmutable;

class BikeRideViewModel extends AbstractViewModel
{
    public ?BikeRide $entity;

    public ?int $type;

    public ?string $title;

    public ?string $content;

    public ?DateTimeImmutable $startAt;

    public ?DateTimeImmutable $endAt;

    public ?int $displayDuration;

    public ?int $closingDuration;

    public ?bool $accessAvailability;

    public ?bool $isRegistrable;

    public ?int $minAge;

    public ?string $displayClass;

    public ?string $btnLabel;

    public ?string $period;

    private ?DateTimeImmutable $today;

    private ?DateTimeImmutable $displayAt;

    private ?DateTimeImmutable $closingAt;

    public static function fromBikeRide(BikeRide $bikeRide, array $services)
    {
        $bikeRideView = new self();
        $bikeRideView->entity = $bikeRide;
        $bikeRideView->title = $bikeRide->getTitle();
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
        $bikeRideView->btnLabel = $bikeRideView->getBtnLabel($services['user']);
        $bikeRideView->period = $bikeRideView->getPeriod($services['appExtention']);
        $bikeRideView->accessAvailability = $bikeRideView->getAccessAvailabity($services['user']);
        $bikeRideView->isRegistrable = $bikeRideView->isRegistrable();

        return $bikeRideView;
    }

    public function isRegistrable(): bool
    {
        if (BikeRide::TYPE_HOLIDAYS === $this->type) {
            return false;
        }

        $today = new DateTime();
        $intervalDisplay = new DateInterval('P'.$this->displayDuration.'D');
        $intervalClosing = new DateInterval('P'.$this->closingDuration.'D');

        return $this->displayAt->sub($intervalDisplay) <= $today && $today <= $this->closingAt->sub($intervalClosing);
    }

    private function getAccessAvailabity(?User $user): bool
    {
        if (BikeRide::TYPE_HOLIDAYS === $this->type) {
            return false;
        }

        $level = (null !== $user) ? $user->getLevel() : null;
        $type = (null !== $level) ? $level->getType() : null;

        return Level::TYPE_FRAME === $type && BikeRide::TYPE_SCHOOL === $this->type && $this->today <= $this->closingAt;
    }

    public function isOver(): bool
    {
        return $this->closingAt < $this->today;
    }

    public function isNext(): bool
    {
        $interval = new DateInterval('P'.$this->displayDuration.'D');

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
        if ($this->getAccessAvailabity($user)) {
            return 'Disponibilité';
        }

        return 'S\'incrire';
    }

    private function getPeriod(AppExtension $appExtension): string
    {
        return  (null === $this->endAt)
            ? $appExtension->formatDateLong($this->startAt)
            : $appExtension->formatDateLong($this->startAt).' au '.$appExtension->formatDateLong($this->endAt);
    }
}
