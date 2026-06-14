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
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(FormType::class, null, [
            'action' => $request->getUri(),
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit']
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $this->addFlash('success', $processor->process($licence));

                return $this->redirectToRoute('admin_registration_list', [
                    'filtered' => true,
                    'p' => $request->query->get('p'),
                ]);
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('components/_dialog.modal.html.twig', [
            'form' => $form->createView(),
            'dialog' => $provider->mapToView($licence),
        ], $response);
    }

    #[Route('/admin/inscription/receive/{licence}', name: 'admin_registration_receive', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'licence')]
    public function adminRegistartionReceive(
        Request $request,
        LicenceReceiveProvider $provider,
        LicenceReceiveProcessor $processor,
        Licence $licence
    ): Response {
        return $this->handleDialogAction($request, $licence, $provider, $processor);
    }

    #[Route('/admin/inscription/reject/{licence}', name: 'admin_registration_reject', methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'licence')]
    public function adminRegistartionReject(
        Request $request,
        LicenceRejectProcessor $processor,
        LicenceRejectProvider $provider,
        Licence $licence
    ): Response {
        return $this->handleDialogAction(
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

        return $this->handleDialogAction(
            request: $request,
            object: $provider->createContextObject($licence),
            provider: $provider,
            processor: $processor,
            formClass: LicenceRegisterType::class,
            formOptions: $provider->getFormOptions($licence)
        );
    }
}
