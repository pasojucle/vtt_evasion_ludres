<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ParameterGroup;
use App\Form\ParameterGroupType;
use App\Repository\ParameterGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParameterController extends AbstractController
{
    /**
     * @Route("/admin/parameters/{parameterGroup}", name="admin_groups_parameter", defaults={"parameterGroup"=null})
     */
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
}
