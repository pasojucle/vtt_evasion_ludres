<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\LicenceAgreement;
use Symfony\Component\Form\FormEvent;
use App\Entity\Enum\AgreementKindEnum;
use Symfony\Component\Form\FormEvents;
use App\Service\ReplaceKeywordsService;
use Symfony\Component\Form\AbstractType;
use App\Dto\DtoTransformer\UserDtoTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class LicenceAgreementType extends AbstractType
{
    public function __construct(
        private ReplaceKeywordsService $replaceKeywordsService,
        private UserDtoTransformer $userDtoTransformer,
    ) {
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $licenceAgreement = $event->getData();
            $form = $event->getForm();
            $user = $this->userDtoTransformer->fromEntity($licenceAgreement->getLicence()->getUser());
            if (AgreementKindEnum::CONSENT === $licenceAgreement->getAgreement()->getKind()) {
                $form
                    ->add('agreed', CheckboxType::class, [
                        'label' => $this->replaceKeywordsService->replace($licenceAgreement->getAgreement()->getContent(), $user),
                        'label_html' => true,
                        'block_prefix' => 'customsimplecheck',
                    ])
                ;
            } else {
                $form
                    ->add('agreed', ChoiceType::class, [
                        'label' => false,
                        'choices' => [
                            'Accepter' => true,
                            'Refuser' => false,
                        ],
                        'expanded' => true,
                        'multiple' => false,
                    ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LicenceAgreement::class,
        ]);
    }
}
