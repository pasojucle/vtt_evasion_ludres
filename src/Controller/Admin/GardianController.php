<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionFormHandler;
use App\Core\Handler\DetailHandler;
use App\Entity\MemberGardian;
use App\Form\GardianType;
use App\State\Gardian\Processor\GardianUpdateProcessor;
use App\State\Gardian\Provider\GardianReadProvider;
use App\State\Gardian\Provider\GardianUpdateProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/responsable', name: 'admin_gardian_')]
class GardianController extends AbstractController
{
    #[Route('/{gardian}', name: 'show', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'gardian')]
    public function show(
        Request $request,
        GardianReadProvider $provider,
        MemberGardian $gardian,
        DetailHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider,
            $gardian,
        );
    }

    #[Route('/edit/{gardian}', name: 'edit', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'gardian')]
    public function adminEdit(
        Request $request,
        GardianUpdateProvider $provider,
        GardianUpdateProcessor $processor,
        MemberGardian $gardian,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $gardian,
            $provider,
            $processor,
            GardianType::class
        );
    }
}
