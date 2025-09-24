<?php


namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailService
{
    public const ACTION_REGISTER = 1;
    public const ACTION_RESET = 2;


    private $projectName;

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly UrlGeneratorInterface $router,
        private readonly UserRepository $userRepository,
        private readonly ParameterBagInterface $parameterBag
    ) {
        $this->projectName = $this->parameterBag->get('PROJECT_NAME');
    }

    public function register(User $user, int $action = self::ACTION_REGISTER)
    {
        $url = $this->router->generate(
            'user_register',
            [
            'uuid' => $user->getUuid(),
        ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        /** @var User $admin */
        $admin = $this->userRepository->findAdmin();

        $template = 'email/register.html.twig';
        if (self::ACTION_RESET === $action) {
            $template = 'email/reset.html.twig';
        }

        $email = (new TemplatedEmail())
            ->from($admin->getEmail())
            ->to(new Address($user->getEmail()))
            ->subject('Votre compte au site ' . $this->projectName)
            ->htmlTemplate($template)
            ->context([
                'projectName' => $this->projectName,
                'user' => $user,
                'url' => $url,
            ])
        ;

        $this->mailer->send($email);
    }
}
