<?php

declare(strict_types=1);

namespace App\Security;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
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
        private UserDtoTransformer $userDtoTransformer
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
        $userDto = $this->userDtoTransformer->fromEntity($user);
        $request->getSession()->set('user_fullName', $userDto->member->fullName);

        if ($user instanceof User && $user->isPasswordMustBeChanged()) {
            return new RedirectResponse($this->urlGenerator->generate('change_password'));
        }

        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            if (preg_match('#\/mon-compte\/inscription\/#', $targetPath)) {
                return new RedirectResponse($this->urlGenerator->generate('user_registration_form', [
                    'step' => 1,
                ]));
            }

            // return new RedirectResponse($targetPath);
        }

        // if ($targetPath = $request->getSession()->get('registrationPath')) {
        //     return new RedirectResponse($targetPath);
        // }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return new RedirectResponse($this->urlGenerator->generate('admin_home'));
        }

        // For example : return new RedirectResponse($this->urlGenerator->generate('some_route'));
        //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
        return new RedirectResponse($this->urlGenerator->generate('home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);

        return new RedirectResponse(
            $this->urlGenerator->generate('app_login')
        );
    }
}
