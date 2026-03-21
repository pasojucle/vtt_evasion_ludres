<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Guest;
use App\Repository\GuestRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class GuestAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private Security $security,
        private GuestRepository $guestRepository,
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $token = $request->query->get('token');
        if (!$token) {
            throw new CustomUserMessageAuthenticationException('Token manquant');
        }

        return new SelfValidatingPassport(
            new UserBadge($token, function (string $userIdentifier) {
                $guest = $this->guestRepository->findOneByValidToken($userIdentifier);
                if (!$guest) {
                    throw new UserNotFoundException();
                }

                return $guest;
            })
        );
    }

    public function supports(Request $request): bool
    {
        return $request->query->has('token');
    }

    public function createAuthenticatedToken(Passport $passport, string $firewallName): TokenInterface
    {
        return new UsernamePasswordToken($passport->getUser(), $firewallName, $passport->getUser()->getRoles());
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): ?Response
    {
        /** @var Guest $guest */
        $guest = $this->security->getUser();
        $identity = $guest->getIdentity();
        if ($identity) {
            $request->getSession()->set('user_fullName', sprintf('%s %s', $identity->getFirstName(), $identity->getName()));
        }
        $currentUrl = $request->getUri();
        $urlComponents = parse_url($currentUrl);

        return new RedirectResponse($urlComponents['path']);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);

        $currentUrl = $request->getUri();
        $urlComponents = parse_url($currentUrl);
        
        return new RedirectResponse($urlComponents['path']);
    }
}
