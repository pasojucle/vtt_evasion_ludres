<?php

namespace App\Form\Admin;

use App\Entity\Enum\LevelType;
use App\Entity\Level;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class LevelSchoolAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Level::class,
            'placeholder' => 'Sélectionnez un niveau',
            
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('le')
                    ->andWhere('le.type = :school')
                    ->andWhere((new Expr())->isNull('le.deletedAt'))
                    ->setParameter('school', LevelType::SCHOOL)
                    ->orderBy('le.title', 'ASC');
            },
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
