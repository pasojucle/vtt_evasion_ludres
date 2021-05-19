<?php

namespace App\Service;

use App\Entity\Health;
use App\Entity\Identity;
use DateTime;
use App\Entity\Licence;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\Security;
use App\Repository\RegistrationStepRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationService
{
    private RegistrationStepRepository $registrationStepRepository;
    private UrlGeneratorInterface $router;
    private FormFactoryInterface $formFactory;
    private SessionInterface $session;
    private $user;
    private EntityManagerInterface $entityManager;

    public function __construct(
        RegistrationStepRepository $registrationStepRepository,
        Security $security,
        UrlGeneratorInterface $router,
        FormFactoryInterface $formFactory,
        SessionInterface $session,
        EntityManagerInterface $entityManager
    )
    {
        $this->registrationStepRepository = $registrationStepRepository;
        $this->user = $security->getUser();
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->session = $session;
        $this->entityManager = $entityManager;
    }

    public function getProgress($type, $step)
    {
        $progress = [];
        $progress['prev'] = null;
        $progress['next'] = null;
        $progress['form'] = null;
        $progress['current'] = null;
        $progress['steps'] = null;

        if (!empty($step)) {
            $steps = $this->registrationStepRepository->findByType($type);
            $stepIndex = $step -1;

            foreach($steps as $key => $registrationStep) {
                if ($key < $stepIndex) {
                    $registrationStep->setClass('is-done');
                    $progress['prev'] = $key+1;
                } elseif ($key === $stepIndex) {
                    $registrationStep->setClass('current');
                    $progress['current'] = $registrationStep;
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
            $progress['form'] = $this->getForm($progress['current'], $type, $step);
        }

        return $progress;
    }

    private function getForm($registrationStep, $type, $step): ?Form
    {
        $form = null;
        if (null === $this->user) {
            $this->user = new User();
        }
        if (null === $this->user->getHealth()) {
            $health = new Health();
            $this->user->setHealth($health);
        }
        if ($this->user->getIdentities()->isEmpty()) {
            $identity = new Identity();
            $this->user->addIdentity($identity);
        }

        if (null !== $registrationStep->getForm()) {
            /*if ('qssport' === $registrationStep->getForm()) {
                if ($this->session->get('qs_sport')) {
                    $data = $this->session->get('qs_sport');
                } else {
                    $values = [];
                    foreach (range(0, 8) as $number) {
                        $values[] = null;
                    }
                    $data = [
                        'values' => $values,
                    ];
                }
            } else {
                $data = null;
            }*/
            dump($this->user);
            $form = $this->formFactory->create(UserType::class, $this->user, [
                'attr' =>[
                    'action' => $this->router->generate('registration_form_validate', ['type' => $type, 'step' => $step]),
                ],
                'type' => $type,
                'current' => $registrationStep,
            ]);
        }

        return $form;
    }

    public function updateUser(Form $form)
    {
        $data = $form->getData();
        if ('qs_sport' === $form->getname()) {
            $medicalCertificateRequired = false;
            foreach ($data['values'] as $field) {
                if (true === $field['value']) {
                    $medicalCertificateRequired = $field['value'];
                    break;
                }
            }
            $this->session->set('qs_sport', $data);
            //$licence =  (null !== $this->user->getLicence()) ? $this->user->getLicence() : new Licence();
            //$licence->setMedicalCertificateRequired($medicalCertificateRequired);
            //$this->user->setLicence($licence);
        } else {
            $method = 'add'.ucfirst($form->getname());
            $this->entityManager->persist($data);
            $this->user->$method($data);
        }

        return $this->user;
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