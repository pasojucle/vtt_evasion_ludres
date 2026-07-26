<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\EmergencyContact;
use App\Form\EmergencyContactType;
use App\State\EmergencyContact\Processor\EmergencyContactUpdateProcessor;
use App\State\EmergencyContact\Provider\EmergencyContactReadProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/emergency/contact', name: 'admin_emergency_contact_')]
class EmergencyContactController extends AbstractCrudController
{
    #[Route('/{emergencyContact}', name: 'show', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'emergencyContact')]
    public function show(
        EmergencyContactReadProvider $provider,
        EmergencyContact $emergencyContact,
    ): Response {
        
        return $this->render('emergency_contact/admin/show.html.twig', [
            'view' => $provider->getStreamView($emergencyContact),
        ]);
    }

    #[Route('/edit/{emergencyContact}', name: 'edit', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'emergencyContact')]
    public function adminEdit(
        Request $request,
        EmergencyContactReadProvider $provider,
        EmergencyContactUpdateProcessor $processor,
        EmergencyContact $emergencyContact,
    ): Response {
        return $this->handleFormComponentAction(
            $request,
            $emergencyContact,
            $provider,
            $processor,
            EmergencyContactType::class
        );
    }
}
