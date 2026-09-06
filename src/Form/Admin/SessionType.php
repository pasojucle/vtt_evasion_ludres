<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Dto\Payload\SessionCreateAdminPayload;
use App\Entity\Licence;
use App\Form\Admin\EventListener\AddSessionSubscriber;
use App\Form\ChoiceProvider\LevelChoiceProvider;
use App\Service\SeasonService;
use App\Service\SessionService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SessionType extends AbstractType
{
    public function __construct(
        private readonly SeasonService $seasonService,
        private readonly SessionService $sessionService,
        private LevelChoiceProvider $levelChoiceProvider,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('season', ChoiceType::class, [
                'label' => 'Saison',
                'multiple' => false,
                'choices' => $this->getSeasonChoices(),
                'autocomplete' => true,
                'attr' => [
                    'data-action' => 'change->form-modifier#change',
                    'data-container-id' => 'admin-session-add',
                    'data-width' => '100%',
                    'data-placeholder' => 'Sélectionnez une saison',
                    'data-language' => 'fr',
                    'data-allow-clear' => true,
                ],
                'required' => false,
            ])
            ->add('level', ChoiceType::class, [
                    'label' => 'Niveau',
                    'choices' => $this->levelChoiceProvider->getChoices(),
                    'autocomplete' => true,
                    'required' => false,
                    'attr' => [
                        'data-action' => 'change->form-modifier#change',
                    ],
            ])
            ->addEventSubscriber(new AddSessionSubscriber($this->sessionService, $this->urlGenerator))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => SessionCreateAdminPayload::class,
        ]);
    }

    private function getSeasonChoices(): array
    {
        $currentSeason = $this->seasonService->getCurrentSeason();
        $seasonChoices = ['Saison ' . $currentSeason => $currentSeason];
        $minSeasonToTakePart = $this->seasonService->getMinSeasonToTakePart();
        if ($minSeasonToTakePart < $currentSeason) {
            $seasonChoices['Saison ' . $minSeasonToTakePart] = $minSeasonToTakePart;
        }
        
        $seasonChoices['licence.filter.testing_in_processing'] = Licence::FILTER_TESTING_IN_PROGRESS;
        $seasonChoices['licence.filter.in_processing'] = Licence::FILTER_IN_PROCESSING;

        return $seasonChoices;
    }
}
