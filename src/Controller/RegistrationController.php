<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\DtoTransformer\RegistrationStepDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\RegistrationStep;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\ContentRepository;
use App\Repository\MembershipFeeRepository;
use App\Security\SelfAuthentication;
use App\Service\MailerService;
use App\Service\MessageService;
use App\Service\ParameterService;
use App\Service\ProjectDirService;
use App\UseCase\Registration\EditRegistration;
use App\UseCase\Registration\GetProgress;
use App\UseCase\Registration\GetRegistrationFile;
use App\UseCase\Registration\GetStatusWarning;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use ZipArchive;

class RegistrationController extends AbstractController
{
    public function __construct(
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

    #[Route('/inscription/{step}', name: 'registration_form', methods: ['GET', 'POST'], requirements:['step' => '\d+'], defaults:['step' => 1])]
    #[Route('/mon-compte/inscription/{step}', name: 'user_registration_form', requirements:['step' => '\d+'], methods: ['GET', 'POST'])]
    public function registerForm(
        Request $request,
        MembershipFeeRepository $membershipFeeRepository,
        ParameterService $parameterService,
        EditRegistration $editRegistration,
        SelfAuthentication $selfAuthentication,
        int $step
    ): Response {
        if ('user_registration_form' === $request->attributes->get('_route') || 1 < $step) {
            $this->denyAccessUnlessGranted('ROLE_USER');
        }
        $session = $request->getSession();
        /** @var User $user */
        $user = $this->getUser();
        if ((int) $session->get('registrationMaxStep') < $step) {
            $session->set('registrationMaxStep', $step);
        }
        $progress = $this->getProgress->execute($step);
    
        if ($user && $progress->nextStep && 'registration_form' === $request->attributes->get('_route')) {
            return $this->redirectToRoute('user_registration_form', ['step' => $step]);
        };

        if (!$progress->nextStep && 'registration_form' === $request->attributes->get('_route') && null !== $this->getUser()) {
            $selfAuthentication->logout();
        }

        if ($progress->redirecToRoute) {
            return $this->redirectToRoute($progress->redirecToRoute);
        }

        $form = $progress->current->formObject;

        $schoolTestingRegistration = $parameterService->getSchoolTestingRegistration();

        if ($step === 1 && !$schoolTestingRegistration['value'] && UserType::FORM_MEMBER === $progress->current->form && !$progress->user->licenceNumber) {
            $message = str_replace(['<p>', '</p>'], '', html_entity_decode($schoolTestingRegistration['message']));
            $this->addFlash('success', $message);
        }

        $maxStep = $step;
        $session->set('registrationMaxStep', $maxStep);

        if (null !== $form) {
            $form->handleRequest($request);
        }

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            [$route, $params] = $editRegistration->execute($request, $form, $progress);

            return $this->redirectToRoute($route, $params);
        }

        return $this->render('registration/registrationForm.html.twig', [
            'step' => $step,
            'progress' => $progress,
            'form' => (null !== $form) ? $form->createView() : null,
            'maxStep' => $request->getSession()->get('registrationMaxStep'),
            'all_membership_fee' => $membershipFeeRepository->findAll(),
            'membership_fee_content' => $this->contentRepository->findOneByRoute('registration_membership_fee')?->getContent(),
            'media' => RegistrationStep::RENDER_VIEW,
        ]);
    }

    #[Route('/inscription/file/{user}', name: 'registration_file', methods: ['GET'])]
    public function registrationFile(
        GetRegistrationFile $getRegistrationFile,
        ProjectDirService $projectDir,
        User $user
    ): Response {
        if (!$registrationFiles = $getRegistrationFile->execute($user)) {
            return $this->render('registration/unregistrable.html.twig', [
                'warning' => sprintf('Le dossier %s ne peux plus être téléchargé.', $user->getLastLicence()->getSeason()),
            ]);
        }
        $zipName = $projectDir->path('tmp', 'inscription_vtt_evasion_ludres.zip');
        if (file_exists($zipName)) {
            unlink($zipName);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipName, ZipArchive::CREATE) === true) {
            foreach ($registrationFiles as $registrationFile) {
                $zip->addFile($registrationFile, basename($registrationFile));
            }
            $zip->close();
        }

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


    #[Route('/inscription/existante', name: 'registration_existing', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function registrationDownload(
        GetStatusWarning $getStatusWarning
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        return $this->render('registration/unregistrable.html.twig', [
            'user_id' => $user->getId(),
            'warning' => $getStatusWarning->execute($user),
        ]);
    }

    #[Route('/reinscription/impossible', name: 'unregistrable_new_saison', methods: ['GET'])]
    public function unregistrableNewSeason(
        MessageService $messageService,
    ): Response {
        return $this->render('registration/unregistrable.html.twig', [
            'warning' => $messageService->getMessageByName('NEW_SEASON_RE_REGISTRATION_DISABLED_MESSAGE'),
         ]);
    }

    #[Route('/inscription/school/testing/disabled', name: 'registration_scholl_testing_disabled', methods: ['GET'], options:['expose' => true])]
    public function schollTestingDisabled(
        ParameterService $parameterService,
    ): Response {
        return $this->render('component/alert.modal.html.twig', [
            'form' => null,
            'title' => 'Inscription école vtt',
            'message' => $parameterService->getSchoolTestingRegistration()['message'],
            'url' => 'contact',
            'anchor_text' => 'Nous contacter',
        ]);
    }

    #[Route('/inscription/error', name: 'registration_error', methods: ['GET'])]
    public function error(
    ): Response {
        return $this->render('registration/unregistrable.html.twig', [
            'warning' => 'Une erreure s\'est produite pendant l\'enregistrement de l\'inscription.',
        ]);
    }
}
