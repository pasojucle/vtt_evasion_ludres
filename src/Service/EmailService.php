<?php


namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mime\Address;
use App\Repository\ParameterRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class EmailService
{
    const ACTION_REGISTER = 1;
    const ACTION_RESET = 2;

    private $mailer;
    private $projectName;
    private $router;
    private $userRepository;

    public function __construct(
        MailerInterface $mailer,
        ParameterRepository $parameterRepository,
        UrlGeneratorInterface $router,
        UserRepository $userRepository
    )
    {
     $this->mailer = $mailer;
     $this->projectName = $parameterRepository->findOneByName('PROJECT_NAME');
     $this->router = $router;
     $this->userRepository = $userRepository;
    }

    public function register(User $user, int $action = self::ACTION_REGISTER)
    {
        $url = $this->router->generate('user_register', [
            'uuid' => $user->getUuid(),
        ],
        UrlGeneratorInterface::ABSOLUTE_URL);

        /** @var User $admin */
        $admin = $this->userRepository->findAdmin();

        $template = 'email/register.html.twig';
        if (self::ACTION_RESET === $action) {
            $template = 'email/reset.html.twig';
        }

        $email = (new TemplatedEmail())
            ->from($admin->getEmail())
            ->to(new Address($user->getEmail()))
            ->subject('Votre compte au site '.$this->projectName->getValue())
            ->htmlTemplate($template)
            ->context([
                'projectName' => $this->projectName->getValue(),
                'user' =>  $user,
                'url' => $url,
            ])
            ;

        $this->mailer->send($email);
    }
}