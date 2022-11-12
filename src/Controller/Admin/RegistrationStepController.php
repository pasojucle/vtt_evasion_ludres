<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\RegistrationStep;
use App\Entity\RegistrationStepGroup;
use App\Form\RegistrationStepType;
use App\Repository\RegistrationStepGroupRepository;
use App\Repository\RegistrationStepRepository;
use App\Service\OrderByService;
use App\UseCase\Registration\GetRegistrationByTypes;
use App\UseCase\RegistrationStep\EditRegistrationStep;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationStepController extends AbstractController
{
    public function __construct(
        private RegistrationStepRepository $registrationStepRepository,
        private RegistrationStepGroupRepository $registrationStepGroupRepository,
        private OrderByService $orderByService
    ) {
    }

    #[Route('/admin/param_inscription', name: 'admin_registration_steps', methods: ['GET'])]
    public function adminRegistrationSteps(
        GetRegistrationByTypes $registrationByTypes
    ): Response {
        list($labels, $registrationByTypes) = $registrationByTypes->execute();

        return $this->render('registration/admin/registrationList.html.twig', [
            'registrationStepGroups' => $this->registrationStepGroupRepository->findAll(),
            'registrationByTypes' => $registrationByTypes,
            'labels' => $labels,
        ]);
    }

    #[Route('/admin/registrationStepGroup/ordonner/{group}', name: 'admin_registration_step_group_order', methods: ['GET', 'POST'], options:['expose' => true])]
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
        if (null !== $newOrder) {
            $regitrationSteps = $this->registrationStepRepository->findByGroup($step->getRegistrationStepGroup());
            $this->orderByService->setNewOrders($step, $regitrationSteps, (int) $newOrder);
        }

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
