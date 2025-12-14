<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Address;
use App\Form\EventListener\AddressSubscriber;
use App\Repository\CommuneRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function __construct(private CommuneRepository $communeRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $required = $options['required'];

        $builder
            ->add('street', TextType::class, [
                'label' => 'Adresse',
                'row_attr' => [
                    'class' => 'form-group-inline full-width ' . $options['row_class'],
                ],
                'attr' => [
                    'data-constraint' => '',
                ],
                'required' => $required,
            ])
            ->addEventSubscriber(new AddressSubscriber($this->communeRepository));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
            'row_class' => '',
            'required' => false,
            'gardian' => '',
        ]);
    }
}
