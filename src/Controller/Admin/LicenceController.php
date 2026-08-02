<?php

declare(strict_types=1);

namespace App\Controller\Admin;


use App\Entity\Licence;
use App\Entity\User;
use App\Form\Admin\LicenceMemberType;
use App\Form\Admin\LicenceRegisterType;
use App\Form\Admin\LicenceRejectType;
use App\State\Licence\Processor\LicenceDeleteProcessor;
use App\State\Licence\Processor\LicenceReceiveProcessor;
use App\State\Licence\Processor\LicenceRegisterProcessor;
use App\State\Licence\Processor\LicenceRejectProcessor;
use App\State\Licence\Processor\LicenceUpdateProcessor;
use App\State\Licence\Provider\LicenceDeleteProvider;
use App\State\Licence\Provider\LicenceReadProvider;
use App\State\Licence\Provider\LicenceReceiveProvider;
use App\State\Licence\Provider\LicenceRegisterProvider;
use App\State\Licence\Provider\LicenceRejectProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LicenceController extends AbstractCrudController
{
    #[Route('/admin/licence/{user}', name: 'admin_licence_show', methods: ['GET'])]
    #[IsGranted('USER_EDIT', 'user')]
    public function show(
        LicenceReadProvider $provider,
        User $user,
    ): Response {
        
        return $this->render('licence/admin/show.html.twig', [
            'view' => $provider->getStreamView($user),
        ]);
    }
    
    #[Route('/admin/licence/edit/{user}', name: 'admin_licence_edit', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'user')]
    public function adminEdit(
        Request $request,
        LicenceReadProvider $provider,
        LicenceUpdateProcessor $processor,
        User $user,
    ): Response {

        return $this->handleFormComponentAction(
            $request,
            $user,
            $provider,
            $processor,
            LicenceMemberType::class
        );
    }

    #[Route('/admin/inscription/delete/{licence}', name: 'admin_licence_delete', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'licence')]
    public function adminDeleteLicence(
        Request $request,
        LicenceDeleteProcessor $processor,
        LicenceDeleteProvider $provider,
        Licence $licence
    ): Response {
        return $this->handleFormComponentAction(
            $request,
            $licence,
            $provider,
            $processor
        );
    }

    #[Route('/admin/inscription/receive/{licence}', name: 'admin_registration_receive', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'licence')]
    public function adminRegistartionReceive(
        Request $request,
        LicenceReceiveProvider $provider,
        LicenceReceiveProcessor $processor,
        Licence $licence
    ): Response {
        return $this->handleFormComponentAction($request, $licence, $provider, $processor);
    }

    #[Route('/admin/inscription/reject/{licence}', name: 'admin_registration_reject', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'licence')]
    public function adminRegistartionReject(
        Request $request,
        LicenceRejectProcessor $processor,
        LicenceRejectProvider $provider,
        Licence $licence
    ): Response {
        return $this->handleFormComponentAction(
            request: $request,
            object: $provider->createContextObject($licence),
            provider: $provider,
            processor: $processor,
            formClass: LicenceRejectType::class,
        );
    }

    #[Route('/admin/inscription/register/{licence}', name: 'admin_registration_register', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'licence')]
    public function adminRegistartionRegister(
        Request $request,
        LicenceRegisterProvider $provider,
        LicenceRegisterProcessor $processor,
        Licence $licence
    ): Response {
        return $this->handleFormComponentAction(
            request: $request,
            object: $provider->createContextObject($licence),
            provider: $provider,
            processor: $processor,
            formClass: LicenceRegisterType::class,
        );
    }
}
