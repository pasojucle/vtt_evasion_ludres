<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Licence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GuestLicenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('FFVelo', CheckboxType::class, [
                'label' => 'Je suis incrit à un club FFVélo',
                'required' => false,
                'attr' => [
                    'data-action' => 'change->form-modifier#change',
                    'data-container-id' => 'bike-ride-licence'
                ],
                'block_prefix' => 'customsimplecheck'
            ]);
            
        $formModifier = function (FormInterface $form, ?bool $isFFVelo) {
            if ($isFFVelo) {
                $form
                    ->add('clubName', TextType::class, [
                        'label' => 'Nom de votre club',
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                        'required' => false,
            ]);
            } else {
                $form->add('clubName', HiddenType::class, [
                    'data' => null,
                ]);
            }
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            /** @var Licence $licence */
            $licence = $event->getData();
            $formModifier($event->getForm(), $licence->isFFVelo());
        });

        $builder->get('FFVelo')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $isFFVelo = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $isFFVelo);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Licence::class,
        ]);
    }
}
