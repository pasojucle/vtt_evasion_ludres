<?php

namespace App\Form;

use App\Entity\Size;
use App\Entity\OrderLine;
use App\Form\QuantityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class OrderLineAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity', QuantityType::class, [
                'data' => 1,
                'row_attr' => [
                    'class' => 'form-group-inline form-group-small',
                ],
            ])
            ->add('size', EntityType::class, [
                'label' => 'Taille',
                'class' => Size::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s');
                },
                'choice_label' => 'name',
                'expanded' => false,
                'multiple' => false,
                'row_attr' => [
                    'class' => 'form-group-inline form-group-small',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => '<i class="fas fa-shopping-cart"></i> Ajouter au panier',
                'label_html' => true,
                'attr' => ['class' => 'btn btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderLine::class,
        ]);
    }
}
