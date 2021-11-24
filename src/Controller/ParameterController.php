<?php

namespace App\Controller;

use App\Entity\ParameterGroup;
use App\Form\ParameterGroupType;
use App\Repository\ParameterGroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParameterController extends AbstractController
{
    /**
     * @Route("/parameters/{parameterGroup}", name="admin_groups_parameter", defaults={"parameterGroup"=null})
     */
    public function list(
        ParameterGroupRepository $parameterGroupRepository,
        ?ParameterGroup $parameterGroup
    ): Response
    {
        $parameterGroups = $parameterGroupRepository->findParameterGroups();

        if (null === $parameterGroup) {
            $parameterGroup = $parameterGroups[0];
        }
        $form = $this->createForm(ParameterGroupType::class, $parameterGroup);

        return $this->render('parameter/list.html.twig', [
            'parameter_group' => $parameterGroup,
            'parameter_groups' => $parameterGroups,
            'form' => $form->createView(),
        ]);
    }
}
