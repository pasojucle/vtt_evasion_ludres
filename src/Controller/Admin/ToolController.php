<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Enum\LicenceStateEnum;
use App\Entity\User;
use App\Form\Admin\ToolType;
use App\Form\Admin\UserSearchType;
use App\Repository\ParameterRepository;
use App\Repository\UserRepository;
use App\Service\MailerService;
use App\Service\MessageService;
use App\Service\PaginatorService;
use App\Service\UserService;
use App\UseCase\CronTab\CronTabLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ToolController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/admin/tool/delete/user', name: 'admin_tool_delete_user', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDeleteUser(): Response
    {
        $form = $this->createForm(UserSearchType::class, null, [
            'attr' => [
                'data-controller' => 'modal-trigger',
                'data-action' => 'submit->modal-trigger#deleteUser',
                'data-modal-trigger-url-value' => $this->generateUrl('admin_tool_confirm_delete_user', ['user' => 0]),
            ],
        ]);

        return $this->render('tool/delete_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/tool/confirm/delete/user/{user}', name: 'admin_tool_confirm_delete_user', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminConfirmDeleteUser(
        Request $request,
        UserService $userService,
        ?User $user,
    ): Response {
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(FormType::class, $user, [
            'action' => $request->getUri(),
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit']
        ]);

        $fullname = $user->getLicenceNumber();
        if ($user->getIdentity()) {
            $fullname .= ' - ' . $user->getIdentity()->getFullName();
        }
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $userService->deleteUser($user);
                $this->addFlash('success', "Les données de l'utilisateur {$fullname} ont bien été supprimées");

                return $this->redirectToRoute('admin_tool_delete_user');
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('component/destructive.modal.html.twig', [
            'title' => 'Supprimer un adhérent',
            'content' => sprintf('Etes vous certain de supprimer l\'utilisateur <b>%s</b> ?', $fullname),
            'btn_label' => 'Supprimer',
            'form' => $form->createView(),
        ], $response);
    }

    #[Route('/admin/registration/error', name: 'admin_registration_error', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminRegistrationError(
        Request $request,
        MailerService $mailerService,
        UserDtoTransformer $userDtoTransformer,
        MessageService $messageService,
        ParameterRepository $parameterRepository,
    ): Response {
        $form = $this->createForm(ToolType::class);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $licence = $data['user']->getLastLicence();
            $user = $userDtoTransformer->identifiersFromEntity($data['user']);
            /** @var SubmitButton $submit */
            $submit = $form->get('submit');
            $content = ($submit->isClicked())
                ? mb_convert_encoding($data['content'], 'UTF-8', mb_list_encodings())
                : $messageService->getMessageByName('EMAIL_REGISTRATION_ERROR');
            $form = $this->createForm(ToolType::class, [
                'user' => $data['user'],
                'content' => $content,
            ]);
            if ($submit instanceof ClickableInterface && $submit->isClicked()) {
                $subject = 'Votre inscription au club de Vtt Évasion Ludres';

                $result = $mailerService->sendMailToMember($user, $subject, $content);
                if ($result['success']) {
                    $licence->setState((LicenceStateEnum::TRIAL_FILE_SUBMITTED === $licence->getState())
                        ? LicenceStateEnum::TRIAL_FILE_PENDING
                        : LicenceStateEnum::YEARLY_FILE_PENDING);
                    $this->entityManager->persist($licence);
                    $this->entityManager->flush();
                } else {
                    $form->addError(new FormError($result['message']));
                }
            }
        }

        return $this->render('tool/registration_error.html.twig', [
            'form' => $form->createView(),
            'settings' => [
                'parameters' => $parameterRepository->findByNames(['EMAIL_REGISTRATION_ERROR']),
            ],
        ]);
    }
    
    #[Route('/admin/outil/export_email', name: 'admin_export_email', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminExportEmail(
        UserRepository $userRepository
    ): Response {
        $users = $userRepository->findMinorAndTesting();
        $content = [];
        $row = ['Prénom', 'Nom', 'Mail', 'Date de naissance', 'Numéro de licence', 'Année', '3 séances d\'essai'];
        $content[] = implode(',', $row);

        if (!empty($users)) {
            foreach ($users as $user) {
                $identity = $user->getIdentity();
                $licence = $user->getLastLicence();
                $row = [$identity->getFirstName(), $identity->getName(), $identity->getEmail(), $identity->getBirthDate()->format('d/m/Y'), $user->getLicenceNumber(), $licence->getSeason(), !$licence->getState()->isYearly()];
                $content[] = implode(',', $row);
            }
        }

        $fileContent = implode(PHP_EOL, $content);

        $response = new Response($fileContent);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'export_email.csv'
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    #[Route('/admin/crontab/logs', name: 'admin_crontab_logs', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminCronrabLog(
        Request $request,
        CronTabLog $cronTabLog,
        PaginatorDtoTransformer $paginatorDtoTransformer,
        PaginatorService $paginatorService,
    ): Response {
        $data = $cronTabLog->read();
        [$logs, $currentPage] = $paginatorService->paginateFromArray($data, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('crontab/admin/list.html.twig', [
            'logs' => $logs,
            'paginator' => $paginatorDtoTransformer->fromArray($data, PaginatorService::PAGINATOR_PER_PAGE, $currentPage),
        ]);
    }
}
