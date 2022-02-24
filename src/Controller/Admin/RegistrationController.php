<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Licence;
use App\Entity\RegistrationStep;
use App\Entity\RegistrationStepGroup;
use App\Entity\User as UserEntity;
use App\Form\RegistrationStepType;
use App\Form\UserType;
use App\Repository\ContentRepository;
use App\Repository\MembershipFeeRepository;
use App\Repository\RegistrationStepGroupRepository;
use App\Repository\RegistrationStepRepository;
use App\Service\LicenceService;
use App\Service\MailerService;
use App\Service\OrderByService;
use App\Service\ParameterService;
use App\Service\PdfService;
use App\Service\RegistrationService;
use App\Service\UploadService;
use App\Service\UserService;
use App\UseCase\Registration\EditRegistration;
use App\UseCase\Registration\GetProgress;
use App\UseCase\RegistrationStep\EditRegistrationStep;
use App\UseCase\RegistrationStep\GetReplaces;
use App\ViewModel\RegistrationStepPresenter;
use App\ViewModel\UserPresenter;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    public function __construct(
        private RegistrationStepRepository $registrationStepRepository,
        private RegistrationStepGroupRepository $registrationStepGroupRepository,
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
        private LicenceService $licenceService,
        private MailerService $mailerService,
        private UserService $userService,
        private UploadService $uploadService,
        private GetReplaces $getReplaces,
        private OrderByService $orderByService,
        private RegistrationService $registrationService,
        private GetProgress $getProgress
    ) {
    }
   
    #[Route('/admin/param_inscription', name: 'admin_registration_steps', methods: ['GET'])]
    public function adminRegistrationSteps(
        Request $request
    ): Response {
        $isFinalValues = [
            'essai' => false,
            'final' => true,
        ];
        $renders = [RegistrationStep::RENDER_VIEW, RegistrationStep::RENDER_FILE];
        $registrationByTypes = [];
        $labels = [];
        foreach (array_keys(Licence::CATEGORIES) as $category) {
            $labels['categories'][] = Licence::CATEGORIES[$category];
            foreach ($isFinalValues as $isFinalLabel => $isFinal) {
                $labels['isFinalLabels'][] = $isFinalLabel;
                foreach ($renders as $render) {
                    $labels['render'][$category][$isFinal][] = (RegistrationStep::RENDER_VIEW === $render)
                        ? '<i class="fas fa-desktop"></i>'
                        : '<i class="fas fa-file-pdf"></i>';
                    $registrationByTypes[$category][$isFinal][$render] = $this->registrationStepRepository->findByCategoryAndFinal($category, $isFinal, $render);
                }
            }
        }

        return $this->render('registration/admin/registrationList.html.twig', [
            'registrationStepGroups' => $this->registrationStepGroupRepository->findAll(),
            'registrationByTypes' => $registrationByTypes,
            'labels' => $labels,
        ]);
    }

    #[Route('/admin/registrationStepGroup/ordonner/{group}', name: 'admin_registration_step_group_order', methods: ['GET', 'POST'],  options:['expose' => true])]
    public function adminregistrationStepGroupOrder(
        Request $request,
        RegistrationStepGroup $group
    ): Response {
        $newOrder = $request->request->get('newOrder');
        $regitrationStepGroups = $this->registrationStepGroupRepository->findAll();

        $this->orderByService->setNewOrders($group, $regitrationStepGroups, $newOrder);

        return $this->redirectToRoute('admin_registration_steps');
    }

    #[Route('/admin/registrationStep/ordonner/{step}', name: 'admin_registration_step_order', methods: ['GET', 'POST'], options:['expose' => true])]
    public function adminregistrationStepOrder(
        Request $request,
        RegistrationStep $step
    ): Response {
        $newOrder = $request->request->get('newOrder');
        $regitrationSteps = $this->registrationStepRepository->findByGroup($step->getRegistrationStepGroup());

        $this->orderByService->setNewOrders($step, $regitrationSteps, $newOrder);

        return $this->redirectToRoute('admin_registration_steps');
    }

    #[Route('/admin/param_inscription/{step}', name: 'admin_registration_step', methods: ['GET', 'POST'])]
    public function adminRegistrationStep(
        Request $request,
        EditRegistrationStep $editRegistrationStep,
        RegistrationStep $step
    ): Response {
        $form = $this->createForm(RegistrationStepType::class, $step);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $editRegistrationStep->execute($request, $form);
        }

        return $this->render('registration/admin/registrationStep.html.twig', [
            'registrationStep' => $step,
            'form' => $form->createView(),
        ]);
    }
}
