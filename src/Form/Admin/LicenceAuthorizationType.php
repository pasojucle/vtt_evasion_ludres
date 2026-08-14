<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\LicenceAgreement;
use App\Service\ReplaceKeywordsService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LicenceAuthorizationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $licenceAuthorization = $event->getData();
            $form = $event->getForm();
            $agreement = $licenceAuthorization->getAgreement();
            $form
                ->add('agreed', CheckboxType::class, [
                    'block_prefix' => 'switch',
                    'attr' => [
                        'data-switch-on' => $agreement->getAuthorizationMessage(),
                        'data-switch-off' => $agreement->getRejectionMessage(),
                    ],
                    'required' => false,
                ]);
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var LicenceAgreement|null $authorization */
        $authorization = $form->getData();

        if ($authorization && $authorization->getAgreement()) {
            $view->vars['label'] = $authorization->getAgreement()->getTitle();
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LicenceAgreement::class,
        ]);
    }
}
