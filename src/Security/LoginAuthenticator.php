<?php
namespace App\Security;

use App\Repository\UserEntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;



class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    // public const LOGIN_ROUTE = 'app_login';
    
    // private UserEntityRepository $userRepository;
    // private UrlGeneratorInterface $urlGenerator;

    // public function __construct(UserEntityRepository $userRepository, UrlGeneratorInterface $urlGenerator)
    // {
    //     $this->userRepository = $userRepository;
    //     $this->urlGenerator = $urlGenerator;
    // }

    // public function authenticate(Request $request): Passport
    // {
    //     // Get the email from the login form (assuming the field is called 'email')
    //     $email = $request->request->get('email', '');
    //     $request->getSession()->set('LAST_EMAIL', $email);

    //     // Fetch the user by email from the repository
    //     $user = $this->userRepository->findOneByEmail($email);

    //     // If user not found, throw an authentication exception
    //     if (!$user) {
    //         throw new AuthenticationException('Invalid credentials.');
    //     }

    //     return new Passport(
    //         new UserBadge($email), // Email used for user badge
    //         new PasswordCredentials($request->request->get('password', '')),
    //         [
    //             new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
    //         ]
    //     );
    // }

    // public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    // {
    //     $user = $token->getUser();
    //     $roles = $user->getRoles();

    //     // Redirect based on roles
    //     if (in_array('ROLE_ADMIN', $roles)) {
    //         return new RedirectResponse($this->urlGenerator->generate('admin_list'));
    //     } elseif (in_array('ROLE_PRESTATAIRE', $roles)) {
    //         return new RedirectResponse($this->urlGenerator->generate('voucher_show')); // Redirection vers 'voucher_show'
    //     } elseif (in_array('ROLE_ARTISAN', $roles)) {
    //         return new RedirectResponse($this->urlGenerator->generate('artisan_dashboard'));
    //     } else {
    //         return new RedirectResponse($this->urlGenerator->generate('user_dashboard'));
    //     }
    // }

    // public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    // {
    //     return new RedirectResponse($this->urlGenerator->generate(self::LOGIN_ROUTE));
    // }

    // protected function getLoginUrl(Request $request): string
    // {
    //     return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    // }

    // public function supports(Request $request): bool
    // {
    //     return $request->attributes->get('_route') === self::LOGIN_ROUTE
    //         && $request->isMethod('POST');
    // }


    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {

        $user = $token->getUser();
    
        // Vérifier les rôles de l'utilisateur
        $roles = $user->getRoles();
        
        // Vérifier si l'utilisateur a le rôle ADMIN
        if (in_array('ROLE_PRESTATAIRE', $roles)) {
            // Rediriger vers la liste des administrateurs
            return new RedirectResponse($this->urlGenerator->generate('app_index'));
        } elseif (in_array('ROLE_ARTISAN', $roles)) {
                        return new RedirectResponse('app_produit');
        } else {
          
            return new RedirectResponse($this->urlGenerator->generate('app_index'));
        }
        // // if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
        // //     return new RedirectResponse($targetPath);
        // // }

        // // For example:
        // return new RedirectResponse($this->urlGenerator->generate('admin_list'));
        // //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
       
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
