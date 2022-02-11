<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Level;
use App\Entity\Licence;
use App\Repository\LevelRepository;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Security;

class UserFilterType extends AbstractType
{
    private LevelRepository $levelRepository;

    private Security $security;

    public function __construct(LevelRepository $levelRepository, Security $security)
    {
        $this->levelRepository = $levelRepository;
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $today = new DateTime();
        $statusChoices = [];
        foreach (range(2021, (int) $today->format('Y')) as $season) {
            $statusChoices['Saison ' . $season] = 'SEASON_' . $season;
        }
        $statusChoices['licence.status.testing_in_processing'] = Licence::STATUS_TESTING_IN_PROGRESS;
        $statusChoices['licence.status.testing_complete'] = Licence::STATUS_TESTING_COMPLETE;

        $builder
            ->add('fullName', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Nom ou prénom',
                ],
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Tous',
                'choices' => [
                    'Licence' => $statusChoices,
                ],
                'attr' => [
                    'class' => 'btn',
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
}
