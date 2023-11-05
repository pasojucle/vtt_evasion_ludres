<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ParameterGroup;
use App\Form\ParameterGroupType;
use App\Form\ParameterType;
use App\Repository\ParameterGroupRepository;
use App\Repository\ParameterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ParameterController extends AbstractController
{
    #[Route('/admin/parameters/{parameterGroup}', name: 'admin_groups_parameter', methods: ['GET', 'POST'], defaults:['parameterGroup' => null])]
    #[IsGranted('ROLE_ADMIN')]
    public function list(
        ParameterGroupRepository $parameterGroupRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        ?ParameterGroup $parameterGroup
    ): Response {
        $parameterGroups = $parameterGroupRepository->findParameterGroups();

        if (null === $parameterGroup) {
            $parameterGroup = $parameterGroups[0];
        }
        $form = $this->createForm(ParameterGroupType::class, $parameterGroup);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $parameterGroup = $form->getData();
            $entityManager->persist($parameterGroup);
            $entityManager->flush();
        }

        return $this->render('parameter/list.html.twig', [
            'parameter_group' => $parameterGroup,
            'parameter_groups' => $parameterGroups,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/admin/parameter/{name}/{redirect}', name: 'admin_parameter_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        ParameterRepository $parameterRepository,
        string $name,
        string $redirect
    ): Response {
        $parameter = $parameterRepository->findOneByName($name);
        if ($parameter) {
            $form = $this->createForm(ParameterType::class, $parameter, [
                'action' => $this->generateUrl($request->attributes->get('_route'), $request->attributes->get('_route_params'), )
            ]);
            $form->handleRequest($request);
            if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();
                return $this->redirectToRoute($redirect);
            }

            return $this->render('parameter/edit.modal.html.twig', [
                'parameter' => $parameter,
                'form' => $form->createView(),
            ]);
        }
        return new Response(null, Response::HTTP_BAD_REQUEST);
    }
}
