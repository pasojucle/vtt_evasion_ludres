<?php

namespace App\Form;

use App\Entity\Level;
use App\Entity\Licence;
use App\Repository\LevelRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


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
        $statusChoices = [
            Licence::STATUS_VALID => 'licence.status.valid',
            Licence::STATUS_WAITING_RENEW => 'licence.status.waiting_renew',
            Licence::STATUS_NONE => 'licence.status.none',
            Licence::STATUS_TESTING_IN_PROGRESS => 'licence.status.testing_in_processing',
            Licence::STATUS_TESTING_COMPLETE => 'licence.status.testing_complete',
        ];
        if ('ROLE_SUPER_USER') {
            $statusChoices[Licence::ALL_USERS] = 'licence.all_users';
        }
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
                'choices' => ['Licence' => array_flip($statusChoices)],
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
        $levelChoices['École VTT']['Toute l\'école VTT'] = Level::TYPE_ALL_MEMBER;
        $levelChoices['Encadrement']['Tout l\'encadrement'] = Level::TYPE_ALL_FRAME;
        $levelChoices['Adultes']['Adultes hors encadrement'] = Level::TYPE_ADULT;
        $levels = $this->levelRepository->findAll();

        if (!empty($levels)) {
            foreach($levels as $level) {
                $type = ($level->getType() === Level::TYPE_MEMBER) ? 'École VTT' : 'Encadrement';
                $levelChoices[$type][$level->getTitle()] = $level->getId();
            }
        }

        return $levelChoices;
    }
}