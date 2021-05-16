<?php

namespace App\Service;

use DateTime;
use Symfony\Component\Security\Core\Security;
use App\Repository\RegistrationStepRepository;

class SubscriptionService
{
    private RegistrationStepRepository $registrationStepRepository;
    private $user;

    public function __construct(RegistrationStepRepository $registrationStepRepository, Security $security)
    {
        $this->registrationStepRepository = $registrationStepRepository;
        $user = $security->getUser();
    }

    public function getProgress($type, $step)
    {
        $steps = $this->registrationStepRepository->findByType($type);

        $progress = [];
        $progress['prev'] = null;
        $progress['next'] = null;
        --$step;

        foreach($steps as $key => $registrationStep) {
            if ($key < $step) {
                $registrationStep->setClass('is-done');
                $progress['prev'] = $key+1;
            } elseif ($key === $step) {
                $registrationStep->setClass('current');
            } else {
                if (null === $progress['next']) {
                    $progress['next'] = $key+1;
                }
            }
            if (null !== $registrationStep->getContent()) {
                $content = $this->replaceFieds($registrationStep->getContent());
                $registrationStep->setContent($content);
            }
            $progress['steps'][$key+1] = $registrationStep;
        }

        return $progress;
    }

    private function replaceFieds(string $content)
    {
        $today = new DateTime();
        $todayStr = $today->format('d/m/Y');
        $fullName = 'Prénom et Nom';
        $bithDate = 'Date de naissance';
        $fullNameChildren = 'Prénom et Nom de l\'enfant';
        $bithDateChildren = 'Date de naissance de l\'enfant';
        $coverage = 'Formule d\'assurance';
        if ($this->user) {

        }

        $fields = [
            ['pattern' => '#(.*)( {{ formule_assurance }})(.*)#s', 'replacement' => "$1 $coverage$3",],
            ['pattern'  => '#(.*)( {{ date }})(.*)#s', 'replacement' => "$1 $todayStr$3",],
            ['pattern'  => '#(.*)( {{ prenom_nom }})(.*)#s', 'replacement' => "$1 $fullName$3",],
            ['pattern'  => '#(.*)( {{ date_de_naissance }})(.*)#s', 'replacement' => "$1 $bithDate$3",],
            ['pattern'  => '#(.*)( {{ prenom_nom_enfant }})(.*)#s', 'replacement' => "$1 $fullNameChildren$3",],
            ['pattern'  => '#(.*)( {{ date_de_naissance_enfant }})(.*)#s', 'replacement' => "$1 $bithDateChildren$3",],
        ];

        foreach ($fields as $field) {
            $content = preg_replace($field['pattern'], $field['replacement'], $content);
        }
        
        
        return $content;
    }
}