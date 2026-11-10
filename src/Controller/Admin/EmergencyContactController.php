<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionFormHandler;
use App\Core\Handler\DetailHandler;
use App\Entity\EmergencyContact;
use App\Form\EmergencyContactType;
use App\State\EmergencyContact\Processor\EmergencyContactUpdateProcessor;
use App\State\EmergencyContact\Provider\EmergencyContactReadProvider;
use App\State\EmergencyContact\Provider\EmergencyContactUpdateProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/emergency/contact', name: 'admin_emergency_contact_')]
class EmergencyContactController extends AbstractController
{
    #[Route('/{emergencyContact}', name: 'show', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'emergencyContact')]
    public function show(
        Request $request,
        EmergencyContactReadProvider $provider,
        EmergencyContact $emergencyContact,
        DetailHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider,
            $emergencyContact,
        );
    }

    #[Route('/edit/{emergencyContact}', name: 'edit', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'emergencyContact')]
    public function adminEdit(
        Request $request,
        EmergencyContactUpdateProvider $provider,
        EmergencyContactUpdateProcessor $processor,
        EmergencyContact $emergencyContact,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $emergencyContact,
            $provider,
            $processor,
            EmergencyContactType::class
        );
    }
}
