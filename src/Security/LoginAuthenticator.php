<?php

declare(strict_types=1);

namespace App\Security;


use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private Security $security,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $login = [];
        $login = $request->request->all('login');
        $password = $login['password'];
        $licenceNumber = $login['licenceNumber'];
        $csrfToken = $login['_csrf_token'];
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $licenceNumber);


        if (!$licenceNumber) {
            throw new CustomUserMessageAuthenticationException('Numéro de licence manquant');
        }

        return new Passport(
            new UserBadge($licenceNumber), // Badge pour transporter l'user
            new PasswordCredentials($password), // Badge pour transporter le password
            [new CsrfTokenBadge('authenticate', $csrfToken)], // Badge pour transporter un token CSRF
        );
    }

    public function supports(Request $request): bool
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function createAuthenticatedToken(Passport $passport, string $firewallName): TokenInterface
    {
        return new UsernamePasswordToken($passport->getUser(), $firewallName, $passport->getUser()->getRoles());
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): ?Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $route = 'home';
        $params = [];

        $targetPath = $this->getTargetPath($request->getSession(), $providerKey);
        if ($targetPath && preg_match('#\/mon-compte\/inscription\/#', $targetPath)) {
            $route = 'user_registration_form';
            $params = ['step' => 1, ];
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $route = 'admin_home';
        }

        $response = new RedirectResponse($this->urlGenerator->generate($route, $params));

        $login = $request->request->all('login');
        if (array_key_exists('skipSplash', $login)) {
            $response->headers->setcookie(new Cookie('skip_splash', $login['skipSplash'], time() + (24 * 60 * 60 * 30)));
        } else {
            $response->headers->clearCookie('skip_splash');
        }

        return $response;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);

        return new RedirectResponse(
            $this->urlGenerator->generate('app_login')
        );
    }
}
