<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\RegistrationStepDtoTransformer;
use App\Entity\RegistrationStep;
use App\Entity\RegistrationStepGroup;
use App\Form\Admin\RegistrationStepType;
use App\Repository\RegistrationStepGroupRepository;
use App\Repository\RegistrationStepRepository;
use App\Service\OrderByService;
use App\UseCase\Registration\GetRegistrationByTypes;
use App\UseCase\RegistrationStep\EditRegistrationStep;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/param', name: 'admin_registration_step', methods: ['GET'])]
class RegistrationStepController extends AbstractController
{
    public function __construct(
        private RegistrationStepRepository $registrationStepRepository,
        private RegistrationStepGroupRepository $registrationStepGroupRepository,
        private OrderByService $orderByService,
        private RegistrationStepDtoTransformer $registrationStepDtoTransformer,
    ) {
    }

    #[Route('/inscription', name: 's', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
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

    #[Route('/Group/ordonner/{group}', name: '_group_order', methods: ['GET', 'POST'], options:['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminregistrationStepGroupOrder(
        Request $request,
        RegistrationStepGroup $group
    ): Response {
        $newOrder = $request->request->get('newOrder');
        $regitrationStepGroups = $this->registrationStepGroupRepository->findAll();

        $this->orderByService->setNewOrders($group, $regitrationStepGroups, $newOrder);

        return $this->redirectToRoute('admin_registration_steps');
    }

    #[Route('/ordonner/{step}', name: '_order', methods: ['GET', 'POST'], options:['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
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

    #[Route('/{step}', name: '', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
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
            'registrationStep' => $this->registrationStepDtoTransformer->fromEntity($step) ,
            'form' => $form->createView(),
        ]);
    }
}
