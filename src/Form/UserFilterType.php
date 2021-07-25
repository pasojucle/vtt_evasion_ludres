<?php

namespace App\Form;

use App\Entity\Level;
use App\Entity\Licence;
use App\Repository\LevelRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class UserFilterType extends AbstractType
{
    private LevelRepository $levelRepository;
    public function __construct(LevelRepository $levelRepository)
    {
        $this->levelRepository = $levelRepository;
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
            ->add('category', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Tous',
                'choices' => array_flip(Licence::CATEGORIES),
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
                    'class' => 'btn btn-ico'
                ]
            ])
            ;
    }

    private function getLevelChoices(): array
    {
        $levelChoices = [];
        $levels = $this->levelRepository->findAll();
        if (!empty($levels)) {
            foreach($levels as $level) {
                $type = ($level->getType() === Level::TYPE_MEMBER) ? 'Adhérent' : 'Encadrement';
                $levelChoices[$type][$level->getTitle()] = $level->getId();
            }
        }

        return $levelChoices;
    }
}