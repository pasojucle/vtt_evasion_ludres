<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\Form\LicenceRegister;
use App\Entity\Licence;
use App\Form\Admin\LicenceRegisterType;
use App\Form\Admin\LicenceRejectType;
use App\Service\LicenceService;
use App\Service\MailerService;
use App\State\Licence\Processor\LicenceDeleteProcessor;
use App\State\Licence\Processor\LicenceReceiveProcessor;
use App\State\Licence\Processor\LicenceRegisterProcessor;
use App\State\Licence\Processor\LicenceRejectProcessor;
use App\State\Licence\Provider\LicenceDeleteProvider;
use App\State\Licence\Provider\LicenceReceiveProvider;
use App\State\Licence\Provider\LicenceRegisterProvider;
use App\State\Licence\Provider\LicenceRejectProvider;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LicenceController extends AbstractCrudController
{
    #[Route('/admin/inscription/delete/{licence}', name: 'admin_delete_licence', methods: ['GET', 'POST'])]
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
