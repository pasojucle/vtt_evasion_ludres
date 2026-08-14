<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\MemberGardian;
use App\Form\GardianType;
use App\State\Gardian\Processor\GardianUpdateProcessor;
use App\State\Gardian\Provider\GardianReadProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/responsable', name: 'admin_gardian_')]
class GardianController extends AbstractCrudController
{
    #[Route('/{gardian}', name: 'show', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'gardian')]
    public function show(
        GardianReadProvider $provider,
        MemberGardian $gardian,
    ): Response {
        return $this->render('gardian/admin/show.html.twig', [
            'view' => $provider->getStreamView($gardian),
        ]);
    }

    #[Route('/edit/{gardian}', name: 'edit', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'gardian')]
    public function adminEdit(
        Request $request,
        GardianReadProvider $provider,
        GardianUpdateProcessor $processor,
        MemberGardian $gardian,
    ): Response {
        return $this->handleFormComponentAction(
            $request,
            $gardian,
            $provider,
            $processor,
            GardianType::class
        );
    }
}
