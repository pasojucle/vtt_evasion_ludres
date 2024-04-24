<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Licence;
use App\Entity\User;
use App\Form\Admin\ToolType;
use App\Form\Admin\UserSearchType;
use App\Repository\ParameterRepository;
use App\Repository\UserRepository;
use App\Service\MailerService;
use App\Service\MessageService;
use App\Service\ParameterService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
            'action' => $this->generateUrl('admin_tool_confirm_delete_user'),
        ]);

        return $this->render('tool/delete_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/tool/confirm/delete/user/{user}', name: 'admin_tool_confirm_delete_user', defaults: ['user' => null], methods: ['GET', 'POST'], options:['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminConfirmDeleteUser(
        Request $request,
        UserService $userService,
        ?User $user
    ): Response {
        if (null !== $user) {
            $form = $this->createForm(FormType::class, null, [
                'action' => $this->generateUrl('admin_tool_confirm_delete_user', [
                    'user' => $user->getId(),
                ]),
            ]);


            $fullname = $user->getLicenceNumber();
            if (null !== $user->GetFirstIdentity()) {
                $fullname .= ' ' . $user->GetFirstIdentity()->getName() . ' ' . $user->GetFirstIdentity()->getFirstName();
            }
            $form->handleRequest($request);
            if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
                $userService->deleteUser($user);
                $this->addFlash('success', "Les données de l'utilisateur {$fullname} ont bien été supprimées");

                return $this->redirectToRoute('admin_tool_delete_user');
            }

            return $this->render('tool/delete_user_modal.html.twig', [
                'form' => $form->createView(),
                'fullname' => $fullname,
                'user' => $user,
            ]);
        }
        return new Response(null, 400);
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
                    $licence->setStatus(Licence::STATUS_IN_PROCESSING);
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
                $identity = $user->getFirstIdentity();
                $licence = $user->getLastLicence();
                $row = [$identity->getFirstName(), $identity->getName(), $identity->getEmail(), $identity->getBirthDate()->format('d/m/Y'), $user->getLicenceNumber(), $licence->getSeason(), !$licence->isFinal()];
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
}
