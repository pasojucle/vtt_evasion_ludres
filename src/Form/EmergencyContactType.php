<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\EmergencyContact;
use App\Validator\Phone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmergencyContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('phone', TextType::class, [
                'label' => 'Télephone de la personne à prévenir en cas d\'urgence',
                'constraints' => [
                    new Phone(),
                ],
                'attr' => [
                    'data-constraint' => 'app-Phone',
                    'data-multiple-fields' => 1,
                    'autocomplete' => 'off',
                    'class' => 'phone-number',
                    'data-form-validator-target' => 'field',
                ],
            ])
            ->add('kinship', TextType::class, [
                'label' => 'Lien de parenté (Mari, Femme, Fils, Fille, Frère, Sœur, Oncle, Tante, Grand parents.....)',
                'attr' => [
                    'data-constraint' => '',
                    'data-form-validator-target' => 'field',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EmergencyContact::class,
        ]);
    }
}
