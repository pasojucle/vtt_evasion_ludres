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
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/param/inscription', name: 'admin_registration_step', methods: ['GET'])]
class RegistrationStepController extends AbstractController
{
    public function __construct(
        private RegistrationStepRepository $registrationStepRepository,
        private RegistrationStepGroupRepository $registrationStepGroupRepository,
        private OrderByService $orderByService,
        private RegistrationStepDtoTransformer $registrationStepDtoTransformer,
    ) {
    }

    #[Route('/list', name: '_list', methods: ['GET'])]
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

        $this->orderByService->setNewOrders($group, $regitrationStepGroups, (int) $newOrder);

        return $this->redirectToRoute('admin_registration_step_list');
    }

    #[Route('/ordonner/{registrationStep}', name: '_order', methods: ['GET', 'POST'], options:['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminregistrationStepOrder(
        Request $request,
        RegistrationStep $registrationStep
    ): Response {
        $newOrder = $request->request->get('newOrder');
        if (null !== $newOrder) {
            $regitrationSteps = $this->registrationStepRepository->findByGroup($registrationStep->getRegistrationStepGroup());
            $this->orderByService->setNewOrders($registrationStep, $regitrationSteps, (int) $newOrder);
        }

        return $this->redirectToRoute('admin_registration_step_list');
    }

    #[Route('/{registrationStep}', name: '_edit', defaults:['registrationStep' => null], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminRegistrationStep(
        Request $request,
        EditRegistrationStep $editRegistrationStep,
        ?RegistrationStep $registrationStep
    ): Response {
        $form = $this->createForm(RegistrationStepType::class, $registrationStep);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $editRegistrationStep->execute($request, $form);
            return $this->redirectToRoute('admin_registration_step_list');
        }

        return $this->render('registration/admin/registrationStep.html.twig', [
            'registrationStep' => $this->registrationStepDtoTransformer->fromEntity($registrationStep),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/supprimer/{registrationStep}', name: '_delete', methods: ['GET', 'POST'])]
    public function adminRegistrationStepDelete(
        Request $request,
        RegistrationStep $registrationStep
    ): Response {
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(FormType::class, null, [
            'action' => $request->getUri(),
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit']
        ]);
        $group = $registrationStep->getRegistrationStepGroup();

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $this->registrationStepRepository->remove($registrationStep, true);

                $registrationSteps = $this->registrationStepRepository->findByGroup($group);
                $this->orderByService->ResetOrders($registrationSteps);

                return $this->redirectToRoute('admin_registration_step_list');
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('component/destructive.modal.html.twig', [
            'title' => 'Supprimer une étape',
            'content' => sprintf('Etes vous certain de supprimer l\'étape %s ?', $registrationStep->getTitle()),
            'btn_label' => 'Supprimer',
            'form' => $form->createView(),
        ], $response);
    }
}
