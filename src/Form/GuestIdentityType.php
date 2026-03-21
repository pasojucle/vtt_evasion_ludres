<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Identity;
use App\Service\LicenceService;
use App\Validator\BirthDate;
use DateInterval;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class GuestIdentityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dateMax = (new DateTime())->sub(new DateInterval('P5Y'));
        $dateMin = (new DateTime())->sub(new DateInterval('P80Y'));
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
                'attr' => ['data-constraint' => ''],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
                'attr' => [
                    'autocomplete' => 'off',
                ],
            ])
            ->add('address', AddressType::class, [
                'required' => true,
                'gardian' => 'guest',
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'Date de naissance',
                'attr' => [
                    'nin' => $dateMin->format('Y-m-d'),
                    'max' => $dateMax->format('Y-m-d'),
                    'data-max-date' => $dateMax->format('Y-m-d'),
                    'data-min-date' => $dateMin->format('Y-m-d'),
                    'data-year-range' => $dateMin->format('Y') . ':' . $dateMax->format('Y'),
                    'autocomplete' => 'off',
                ],
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'constraints' => [
                    new BirthDate(),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Identity::class,
        ]);
    }
}
