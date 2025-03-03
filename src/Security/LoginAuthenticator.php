<?php

namespace App\Security;

use App\Entity\UserEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private EntityManagerInterface $entityManager;

    public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $entityManager)
    {
        $this->urlGenerator = $urlGenerator;
        $this->entityManager = $entityManager;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->getPayload()->getString('email');
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        // Retrieve user from database
        $user = $this->entityManager->getRepository(UserEntity::class)->findOneBy(['email' => $email]);

        // ✅ Check if user is banned and prevent login
        if ($user && $user->isBanned()) {
            throw new CustomUserMessageAuthenticationException('Votre compte a été banni.');
        }

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->getPayload()->getString('password')),
            [
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $session = $request->getSession();
        $user = $token->getUser();
        $roles = $user->getRoles();

        // Vérifier s'il y a une URL de redirection en session (cas réservation)
        if ($session->has('redirect_url')) {
            $redirectUrl = $session->get('redirect_url');
            return new RedirectResponse($redirectUrl);
        }

        // Redirection selon le rôle
        if (in_array('ROLE_ADMIN', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('app_list_user'));
        }
        elseif (in_array('ROLE_PRESTATAIRE', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('app_activite_backoffice'));
        } elseif (in_array('ROLE_ARTISAN', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('app_produit'));
        }

        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    protected function getLoginUrl(Request $request): string
    {
        $session = $request->getSession();

        if ($request->query->has('redirect')) {
            $session->set('redirect_url', $request->query->get('redirect'));
        }
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
