<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\LicenceConsent;
use App\Service\ReplaceKeywordsService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsentType extends AbstractType
{
    public function __construct(
        private ReplaceKeywordsService $replaceKeywordsService,
        private UserDtoTransformer $userDtoTransformer,
    )
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $licenceConsent = $event->getData();
            $form = $event->getForm();
            $user = $licenceConsent->getConsent()->getUser();
            $form
                ->add('value', CheckboxType::class, [
                    'label' => $licenceConsent->getConsent()->getContent(),
                    'label_html' => true,
                ])
            ;
        });
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LicenceConsent::class,
        ]);
    }
}
