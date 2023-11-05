<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\DtoTransformer\RegistrationStepDtoTransformer;
use App\Entity\Licence;
use App\Entity\RegistrationStep;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\ContentRepository;
use App\Repository\MembershipFeeRepository;
use App\Service\ParameterService;
use App\UseCase\Registration\EditRegistration;
use App\UseCase\Registration\GetProgress;
use App\UseCase\Registration\GetRegistrationFile;
use App\UseCase\Registration\GetStatusWarning;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RegistrationController extends AbstractController
{
    public function __construct(
        private RequestStack $requestStack,
        private GetProgress $getProgress,
        private ContentRepository $contentRepository
    ) {
    }

    #[Route('/inscription/info', name: 'registration_detail', methods: ['GET'])]
    public function registrationDetail(
        ContentRepository $contentRepository
    ): Response {
        return $this->render('registration/detail.html.twig', [
            'content' => $contentRepository->findOneByRoute('registration_detail'),
        ]);
    }

    #[Route('/inscription/tarifs', name: 'registration_membership_fee', methods: ['GET'])]
    public function registrationMemberShipFee(
        MembershipFeeRepository $membershipFeeRepository,
        ContentRepository $contentRepository
    ): Response {
        return $this->render('registration/membership_fee_page.html.twig', [
            'all_membership_fee' => $membershipFeeRepository->findAll(),
            'content' => $contentRepository->findOneByRoute('registration_membership_fee'),
        ]);
    }

    #[Route('/inscription/tuto', name: 'registration_tuto', methods: ['GET'])]
    public function registrationTuto(
        ContentRepository $contentRepository
    ): Response {
        return $this->render('registration/tuto.html.twig', [
            'content' => $contentRepository->findOneByRoute('registration_tuto'),
        ]);
    }

    #[Route('/inscription/{step}', name: 'registration_form', methods: ['GET', 'POST'], defaults:['step' => 1])]
    #[Route('/mon-compte/inscription/{step}', name: 'user_registration_form', methods: ['GET', 'POST'])]
    public function registerForm(
        Request $request,
        MembershipFeeRepository $membershipFeeRepository,
        ParameterService $parameterService,
        EditRegistration $editRegistration,
        int $step
    ): Response {
        $session = $this->requestStack->getSession();
        if ('registration_form' === $request->attributes->get('_route') && null !== $this->getUser()) {
            return $this->redirectToRoute('user_registration_form', ['step' => $step]);
        }

        if ((int) $session->get('registrationMaxStep') < $step) {
            $this->requestStack->getSession()->set('registrationMaxStep', $step);
        }

        $progress = $this->getProgress->execute($step);
        $user = $progress['user'];
        if (Licence::STATUS_IN_PROCESSING < $user->lastLicence->status && UserType::FORM_REGISTRATION_FILE !== $progress['current']->form) {
            return $this->redirectToRoute('registration_download', [
                'user' => $user->id,
            ]);
        }
        $form = $progress['current']->formObject;

        $schoolTestingRegistration = $parameterService->getSchoolTestingRegistration($progress['user']);
        if (!$schoolTestingRegistration['value'] && UserType::FORM_MEMBER === $progress['current']->form && !$progress['user']->licenceNumber) {
            $message = str_replace(['<p>', '</p>'], '', html_entity_decode($schoolTestingRegistration['message']));
            $this->addFlash('success', $message);
        }
        $maxStep = $step;
        $session->set('registrationMaxStep', $maxStep);

        if (null !== $form) {
            $form->handleRequest($request);
        }

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $editRegistration->execute($request, $form, $progress);
            
            return $this->redirectToRoute($request->attributes->get('_route'), [
                'step' => $progress['nextIndex'],
            ]);
        }

        return $this->render('registration/registrationForm.html.twig', [
            'step' => $step,
            'steps' => $progress['steps'],
            'form' => (null !== $form) ? $form->createView() : null,
            'template' => $progress['current']->template,
            'prev' => $progress['prevIndex'],
            'current' => $progress['current'],
            'next' => $progress['nextIndex'],
            'maxStep' => $this->requestStack->getSession()->get('registrationMaxStep'),
            'all_membership_fee' => $membershipFeeRepository->findAll(),
            'membership_fee_content' => $this->contentRepository->findOneByRoute('registration_membership_fee')?->getContent(),
            'user' => $progress['user'],
            'media' => RegistrationStep::RENDER_VIEW,
        ]);
    }

    #[Route('/inscription/telechargement/{user}', name: 'registration_download', methods: ['GET'])]
    #[IsGranted('USER_EDIT', 'user')]
    public function registrationDownload(
        GetStatusWarning $getStatusWarning,
        User $user
    ): Response {
        return $this->render('registration/download.html.twig', [
            'user_id' => $user->getId(),
            'warning' => $getStatusWarning->execute($user),
        ]);
    }

    #[Route('/inscription/file/{user}', name: 'registration_file', methods: ['GET'])]
    public function registrationFile(
        GetRegistrationFile $getRegistrationFile,
        User $user
    ): Response {
        $zipName = $getRegistrationFile->execute($user);
        $fileContent = file_get_contents($zipName);
        $response = new Response($fileContent);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            basename($zipName)
        );

        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'application/zip');

        return $response;
    }
 
    #[Route('/inscription/notice/{registrationStep}', name: 'registration_notice', methods: ['GET'])]
    public function registrationNotice(
        RegistrationStepDtoTransformer $registrationStepDtoTransformer,
        RegistrationStep $registrationStep
    ): Response {
        if ($registrationStep->getFilename()) {
            $registrationStepDto = $registrationStepDtoTransformer->fromEntity($registrationStep);
            if ($registrationStepDto->pdfPath && file_exists($registrationStepDto->pdfPath)) {
                $fileContent = file_get_contents($registrationStepDto->pdfPath);
                $response = new Response($fileContent);
                $disposition = HeaderUtils::makeDisposition(
                    HeaderUtils::DISPOSITION_INLINE,
                    'inscription_vtt_evasion_ludres.pdf'
                );

                $response->headers->set('Content-Disposition', $disposition);
                $response->headers->set('Content-Type', 'application/pdf');

                return $response;
            }
        }
        return new Response(null, Response::HTTP_NOT_FOUND);
    }
}
