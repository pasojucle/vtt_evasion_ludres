<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Level;
use App\Entity\Licence;
use App\Repository\LevelRepository;
use App\Service\LicenceService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class UserFilterType extends AbstractType
{
    public const STATUS_TYPE_MEMBER = 1;
    public const STATUS_TYPE_REGISTRATION = 2;
    public const STATUS_TYPE_COVERAGE = 3;

    public function __construct(
        private LevelRepository $levelRepository,
        private Security $security,
        private LicenceService $licenceService
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Nom ou prénom',
                ],
                'required' => false,
            ])
            ->add('level', ChoiceType::class, [
                'label' => false,
                'choices' => $this->getLevelChoices(),
                'placeholder' => 'Tous',
                'attr' => [
                    'class' => 'btn',
                ],
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => '<i class="fas fa-search"></i>',
                'label_html' => true,
                'attr' => [
                    'class' => 'btn btn-ico',
                ],
            ])
            ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();

            $statusChoices = match ($options['statusType']) {
                self::STATUS_TYPE_MEMBER => $this->getMemberStatusChoices(),
                self::STATUS_TYPE_REGISTRATION => $this->getRegistrationStatusChoices(),
                default => null,
            };

            if ($statusChoices) {
                $form
                ->add('status', ChoiceType::class, [
                    'label' => false,
                    'placeholder' => 'Tous',
                    'choices' => $statusChoices,
                    'attr' => [
                        'class' => 'btn',
                    ],
                    'required' => false,
                ]);
            }
        });
    }

    private function getMemberStatusChoices(): array
    {
        $statusChoices = [];
        foreach (range(2021, $this->licenceService->getCurrentSeason()) as $season) {
            $statusChoices['Saison '.$season] = 'SEASON_'.$season;
        }

        return array_reverse($statusChoices);
    }

    private function getRegistrationStatusChoices(): array
    {
        return [
            'licence.status.testing_in_processing' => Licence::STATUS_TESTING_IN_PROGRESS,
            'licence.status.testing_complete' => Licence::STATUS_TESTING_COMPLETE,
            'licence.status.new' => Licence::STATUS_NEW,
            'licence.status.renew' => Licence::STATUS_RENEW,
        ];
    }

    private function getLevelChoices(): array
    {
        $levelChoices = [];
        $levelChoices['École VTT']['Toute l\'école VTT'] = Level::TYPE_ALL_MEMBER;
        $levelChoices['Encadrement']['Tout l\'encadrement'] = Level::TYPE_ALL_FRAME;
        $levelChoices['Adultes']['Adultes hors encadrement'] = Level::TYPE_ADULT;
        $levels = $this->levelRepository->findAll();

        if (!empty($levels)) {
            foreach ($levels as $level) {
                $type = (Level::TYPE_MEMBER === $level->getType()) ? 'École VTT' : 'Encadrement';
                $levelChoices[$type][$level->getTitle()] = $level->getId();
            }
        }

        return $levelChoices;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'statusType' => self::STATUS_TYPE_MEMBER,
        ]);
    }
}
