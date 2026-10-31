<?php

namespace App\Form\Admin;

use App\Entity\Agreement;
use App\Entity\Enum\AgreementKindEnum;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\LicenceMembershipEnum;
use App\Entity\RegistrationStep;
use App\Form\Type\IconType;
use App\Form\Type\TiptapType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Unique;

class AgreementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', TextType::class, [
                'label' => 'Nom (en Upper camel case)',
                'attr' => [
                    'placeholder' => 'IDENTIFIANT_EN_MAJUCULE',
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('content', TiptapType::class, [
                'label' => 'Contenu',
                'config_name' => 'base',
            ])
            ->add('category', EnumType::class, [
                'label' => 'Catégorie',
                'class' => LicenceCategoryEnum::class,
                'autocomplete' => true,
            ])
            ->add('membership', EnumType::class, [
                'label' => 'Adhésion',
                'class' => LicenceMembershipEnum::class,
                'autocomplete' => true,
            ])
            ->add('kind', EnumType::class, [
                'label' => 'Type',
                'class' => AgreementKindEnum::class,
                'autocomplete' => true,
                'attr' => [
                    'data-action' => 'change->form-modifier#change',
                    'data-container-id' => 'agreement-kind'
                ],
            ])
            ->add('registrationSteps', EntityType::class, [
                'label' => 'Étape d\'inscription',
                'class' => RegistrationStep::class,
                'choice_label' => 'title',
                'multiple' => true,
                'autocomplete' => true,
                'required' => false,
            ])
        ;
        $formModifier = function (FormInterface $form, AgreementKindEnum $agreement) {
            if (AgreementKindEnum::AUTHORIZATION === $agreement) {
                $form->add('authorizationMessage', TextType::class, [
                    'label' => 'Message d\'autorisation',
                ])
                ->add('rejectionMessage', TextType::class, [
                    'label' => 'Message de refus',
                ])
                ->add('authorizationIcon', IconType::class, [
                    'label' => 'Icône d\'autorisation',
                ])
                ->add('rejectionIcon', IconType::class, [
                    'label' => 'Icône de refus',
                ]);
            } else {
                $form
                    ->remove('authorizationMessage')
                    ->remove('rejectionMessage')
                    ->remove('authorizationIcon')
                    ->remove('rejectionIcon');
            }
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            /** @var Agreement $agreement */
            $agreement = $event->getData();
            $formModifier($event->getForm(), $agreement->getKind());
        });

        $builder->get('kind')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $kind = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $kind);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Agreement::class,
        ]);
    }
}
