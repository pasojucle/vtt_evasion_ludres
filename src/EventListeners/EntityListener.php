<?php

namespace App\EventListeners;

use DateTime;
use App\Entity\User;
use App\Entity\Licence;
use App\Service\LicenceService;
use App\Entity\RegistrationStep;
use App\Entity\RegistrationStepContent;
use App\DataTransferObject\User as UserDto;
use Symfony\Component\Security\Core\Security;
use Doctrine\Persistence\Event\LifecycleEventArgs;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;



class EntityListener
{
    private string $route;
    private ?User $user;
    private LicenceService $licenceService;
    private TranslatorInterface $translator;
    private string $currentseason;

    public function __construct(
        RequestStack $requestStack,
        Security $security, 
        LicenceService $licenceService,
        TranslatorInterface $translator
    )
    {
        $this->route = $requestStack->getCurrentRequest()->get('_route');
        $this->user = $security->getUser();
        $this->licenceService = $licenceService;
        $this->currentseason = $this->licenceService->getCurrentSeason();
        $this->translator = $translator;
    }

    public function postLoad(RegistrationStepContent $registrationStepContent, LifecycleEventArgs $event)
    {
        if (in_array($this->route, ['registration_form', 'user_registration_form']) && null !== $registrationStepContent->getContent())
        {
            $content = $this->replaceFieds($registrationStepContent->getContent());
            $registrationStepContent->setContent($content);
        }
    }

    private function replaceFieds(string $content)
    {
        if (!empty($content)) {
            $today = new DateTime();
            $todayStr = $today->format('d/m/Y');
            $fullName = 'Prénom et Nom';
            $bithDate = 'Date de naissance';
            $fullNameChildren = 'Prénom et Nom de l\'enfant';
            $bithDateChildren = 'Date de naissance de l\'enfant';
            $coverage = 'Formule d\'assurance';
            if ($this->user) {
                /**@var UserDto $user */
                $user = new UserDto($this->user);
                $fullName = $user->getFullName();
                // $bithDate = $user->getBithDate();
                $fullNameChildren = $user->getFullNameChildren();
                // $bithDateChildren = $user->getBithDateChildren();
                // $coverage = $this->translator->trans(Licence::COVERAGES[$user->getCoverage($this->currentseason)]);
            }

            $fields = [
                // ['pattern' => '#(.*)( {{ formule_assurance }})(.*)#s', 'replacement' => "$1 $coverage$3",],
                // ['pattern'  => '#(.*)( {{ date }})(.*)#s', 'replacement' => "$1 $todayStr$3",],
                ['pattern'  => '#(.*)( {{ prenom_nom }})(.*)#s', 'replacement' => "$1 $fullName$3",],
                // ['pattern'  => '#(.*)( {{ date_de_naissance }})(.*)#s', 'replacement' => "$1 $bithDate$3",],
                ['pattern'  => '#(.*)( {{ prenom_nom_enfant }})(.*)#s', 'replacement' => "$1 $fullNameChildren$3",],
                // ['pattern'  => '#(.*)( {{ date_de_naissance_enfant }})(.*)#s', 'replacement' => "$1 $bithDateChildren$3",],
            ];

            foreach ($fields as $field) {
                $content = preg_replace($field['pattern'], $field['replacement'], $content);
            }
        }
        
        
        return $content;
    }
}