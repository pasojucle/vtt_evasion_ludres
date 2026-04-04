<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class SessionGuestAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rulesApproval', CheckboxType::class, [
                'label' => 'J\'accepte le règlement de la randonnée inscrit ci-dessus.',
                'block_prefix' => 'customsimplecheck',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-modifier',
                    'data-modifier' => 'link_container',
                ],
            ])
            ->add('healthApproval', CheckboxType::class, [
                'label' => 'J\'atteste sur l\'honneur, avoir pris connaissance du questionnaire de santé et des règles d’or. Être en condition physique suffisante pour effectuer le parcours que j\'ai choisi. Avoir pris connaissance des difficultés du parcours et des consignes de sécurité..',
                'block_prefix' => 'customsimplecheck',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-modifier',
                    'data-modifier' => 'link_container',
                ],
            ])
        ;

        $formModifier = function (FormInterface $form, bool $isRulesApproved, bool $isHealthApproved) {
            $attr = ['class' => 'btn btn-primary'];
            if ($isRulesApproved && $isHealthApproved) {
                $attr['onclick'] = "window.location.href='https://www.payasso.fr/evasion-de-ludres/paiements'";
            } else {
                $attr['class'] = 'btn btn-primary disabled';
            }
            $form
                ->add('link', ButtonType::class, [
                    'label' => '<i class="fa-regular fa-circle-right"></i> S\'inscrire',
                    'label_html' => true,
                    'attr' => $attr,
                ]);
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            $formModifier($event->getForm(), false, false);
        });

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getData();
                $isRulesApproved = array_key_exists('rulesApproval', $data) ? (bool) $data['rulesApproval'] : false;
                $isHealthApproved = array_key_exists('healthApproval', $data) ? (bool) $data['healthApproval'] : false;


                $formModifier($event->getForm(), $isRulesApproved, $isHealthApproved);
            }
        );
    }
}
