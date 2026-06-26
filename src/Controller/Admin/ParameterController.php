<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\View\SheetView;
use App\Entity\Parameter;
use App\Entity\Section;
use App\Form\ParameterGroupType;
use App\Form\ParameterType;
use App\Repository\ParameterRepository;
use App\Repository\SectionRepository;
use App\State\Parameter\Processor\ParameterUpdateProcessor;
use App\State\Parameter\Provider\ParameterUpdateProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ParameterController extends AbstractCrudController
{
    #[Route('/admin/maintenace', name: 'admin_service', methods: ['GET', 'POST'], defaults:['parameterGroup' => null])]
    #[IsGranted('ROLE_ADMIN')]
    public function list(
        SectionRepository $parameterGroupRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        ?Section $parameterGroup
    ): Response {
        $parameterGroup = $parameterGroupRepository->findoneById('MAINTENANCE');

        $form = $this->createForm(ParameterGroupType::class, $parameterGroup);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $parameterGroup = $form->getData();
            $entityManager->persist($parameterGroup);
            $entityManager->flush();
        }

        return $this->render('parameter/list.html.twig', [
            'parameter_group' => $parameterGroup,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/admin/parameter/{parameter}', name: 'admin_parameter_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        Request $request,
        ParameterUpdateProvider $provider,
        ParameterUpdateProcessor $processor,
        Parameter $parameter
    ): Response {
        return $this->handleFormComponentAction(
            $request,
            $parameter,
            $provider,
            $processor,
            ParameterType::class
        );
    }
}
