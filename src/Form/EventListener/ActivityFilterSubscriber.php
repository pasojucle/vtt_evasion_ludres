<?php

declare(strict_types=1);

namespace App\Form\EventListener;

use App\Dto\Enum\ActivityPeriod;
use DateTime;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class ActivityFilterSubscriber implements EventSubscriberInterface
{
    private const array MONTHS = [
        1 => 'Janv.',
        2 => 'Fév.',
        3 => 'Mars',
        4 => 'Avril',
        5 => 'Mai',
        6 => 'Juin',
        7 => 'Juill.',
        8 => 'Août',
        9 => 'Sept.',
        10 => 'Oct.',
        11 => 'Nov.',
        12 => 'Déc.',
    ];

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ];
    }

    public function preSetData(FormEvent $event): void
    {
        $filter = $event->getData();

        $month = $this->getComputedMonth($filter->period, $filter->month);

        $this->modifier($event->getForm(), $month);
    }

    public function preSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $period = array_key_exists('period', $data) ? ActivityPeriod::tryFrom($data['period']) : null;
        $month = array_key_exists('month', $data) ? $data['month'] : null;
        $month = $this->getComputedMonth($period, $month);
        $data['month'] = $month;
        $event->setData($data);

        $this->modifier($event->getForm(), $month);
    }

    private function modifier(FormInterface $form, ?string $month): void
    {
        if (null !== $month) {
            $form
                ->add('month', ChoiceType::class, [
                    'label' => false,
                    'choices' => $this->getPreviousAndFollowingMonths($month),
                    'choice_attr' => function ($choice, string $key, mixed $value) {
                        return [
                            'data-action' => 'change->filter#submit'
                        ];
                    },
                    'block_prefix' => 'month_filter',
                    'expanded' => true,
                    'multiple' => false,
                ])
            ;
        } else {
            $form->add('month', HiddenType::class);
        }
    }


    private function getComputedMonth(?ActivityPeriod $period, ?string $computedMonth): ?string
    {
        if (ActivityPeriod::MONTH === $period) {
            $today = new DateTime();
            if (!$computedMonth) {
                $computedMonth = sprintf('%s-%s', $today->format('Y'), $today->format('m'));
            }

            return $computedMonth;
        }

        return null;
    }

    private function getPreviousAndFollowingMonths(string $computedMonth): array
    {
        $date = new \DateTimeImmutable($computedMonth . '-01');

        return [
            'lucide:chevron-left' => $date->modify('-1 month')->format('Y-m'),
            self::MONTHS[(int) $date->format('n')] => $computedMonth,
            'lucide:chevron-right' => $date->modify('+1 month')->format('Y-m'),
        ];
    }
}
