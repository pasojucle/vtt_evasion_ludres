<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/admin/competences', name: 'admin_user_skill_')]
class UserSkillController extends AbstractController
{
    #[Route(path: '/edit/{user}', name: 'edit', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function edit(UserDtoTransformer $userDtoTransformer, User $user): Response
    {
        return $this->render('user_skill/admin/list.html.twig', [
            'user' => $userDtoTransformer->getHeaderFromEntity($user),
        ]);
    }

    // #[Route(path: '/edit/{cluster}/{skill}', name: 'edit', methods: ['GET', 'POST'], options: ['expose' => true])]
    // public function delete(Request $request, Cluster $cluster, Skill $skill): JsonResponse
    // {
    //     $form = $this->api->createForm($request, FormType::class, $skill);
    //     $form->handleRequest($request);
    //     if ($request->getMethod('post') && $form->isSubmitted() && $form->isValid()) {
    //         $response = $this->api->responseForm($skill, $this->transformer, 'idASC', true, 'cluster_skill');
    //         $cluster->removeSkill($skill);
    //         $this->entityManager->flush();
    //         return $response;
    //     }
        
    //     $message = sprintf('<p>Etes vous certain de supprimer la compétence ? %s</p>', $skill->getContent());
    //     return $this->api->renderModal($form, 'Supprimer la compétence', 'Supprimer', 'danger', $message);
    // }
}
