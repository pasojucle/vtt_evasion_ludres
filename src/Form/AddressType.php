<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Address;
use App\Entity\Commune;
use App\Repository\CommuneRepository;
use App\Service\GeoService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class AddressType extends AbstractType
{
    public function __construct(private GeoService $geoService, private CommuneRepository $communeRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $required = $options['required'];

        $builder
            ->add('street', TextType::class, [
                'label' => 'Adresse',
                'row_attr' => [
                    'class' => 'form-group-inline full-width' . $options['row_class'],
                ],
                'attr' => [
                    'data-constraint' => '',
                ],
                'required' => $required,
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'constraints' => [
                    new Length([
                        'min' => 5,
                        'max' => 5,
                    ]), ],
                'row_attr' => [
                    'class' => 'form-group-inline' . $options['row_class'],
                ],
                'attr' => [
                    'data-constraint' => 'app-PostalCode',
                    'class' => 'form-modifier',
                ],
                'required' => $required,
            ])
            
        ;

        $formModifier = function (FormInterface $form, ?string $postalCode, array $options) {
            $form
                ->add('commune', EntityType::class, [
                    'label' => 'Ville',
                    'class' => Commune::class,
                    'choice_label' => 'name',
                    'choices' => $this->communes($postalCode),
                    'placeholder' => 'Sélectionner une commune',
                    'row_attr' => [
                        'class' => 'form-group-inline' . $options['row_class'],
                        'id' => 'commune-' . $form->getParent()->getName(),
                    ],
                    'attr' => [
                        'data-constraint' => '',
                    ],
                    'required' => $options['required'],
                ])
                ;
        };


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier, $options) {
            $form = $event->getForm();
            $data = $event->getData();

            $formModifier($form, $data?->getPostalCode(), $options);
        });

        $builder->get('postalCode')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier, $options) {
                $postalCode = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $postalCode, $options);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
            'row_class' => '',
            'required' => '',
            'row_id' => null,
        ]);
    }

    private function communes(?string $postalCode): array
    {
        $communes = (!empty($postalCode)) ? $this->geoService->getCommunesByPostalCode($postalCode) : [];
        $choices = [];
        if (!empty($communes)) {
            $communeCodes = array_column($communes, 'code');
            
            return $this->communeRepository->findByCodes($communeCodes);
        }

        return array_flip($choices);
    }
}
