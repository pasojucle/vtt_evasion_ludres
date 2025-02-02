<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Level;
use App\Entity\Session;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionType extends AbstractType
{
    private int $levelType;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $submitLabel = (true === $options['is_writable_availability']) ? 'Enregistrer' : 'S\'inscrire';

        $builder
            ->add('session', SessionEditType::class, [
                'label' => false,
                'clusters' => $options['clusters'],
                'is_writable_availability' => $options['is_writable_availability'],
                'display_bike_kind' => $options['display_bike_kind'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => '<i class="fas fa-chevron-circle-right"></i> ' . $submitLabel,
                'label_html' => true,
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $data = $event->getData();
            /** @var Session $session */
            $session = $data['session'];

            $this->levelType = $session->getUser()->getLevel()->getType();

            $showSurvey = ($options['is_writable_availability'])
            ? Level::TYPE_FRAME === $this->levelType && AvailabilityEnum::REGISTERED === $session->getAvailability()
            : true;

            $responsesClass = (!$showSurvey || null === $data['responses']) ? 'd-none' : '';

            $event->getForm()
                ->add('responses', SurveyResponsesType::class, [
                    'label' => false,
                    'attr' => [
                        'class' => $responsesClass,
                    ],
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'clusters' => [],
            'is_writable_availability' => false,
            'display_bike_kind' => false,
        ]);
    }
}
