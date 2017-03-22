<?php
namespace UserBundle\Security;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Core\Security;

class FormLoginAuthenticator extends AbstractFormLoginAuthenticator
{
    private $router;
    private $encoder;
    private $entityManager;
    private $session;
    public function __construct(RouterInterface $router, UserPasswordEncoderInterface $encoder, EntityManager $entityManager, Session $session)
    {
        $this->router = $router;
        $this->encoder = $encoder;
        $this->entityManager = $entityManager;
        $this->session = $session;
    }
    public function getCredentials(Request $request)
    {
        if ($request->getPathInfo() != '/login_check') {
            return;
        }
        $email = $request->request->get('_email');
        $request->getSession()->set(Security::LAST_USERNAME, $email);
        $password = $request->request->get('_password');
        return [
            'email' => $email,
            'password' => $password,
        ];
    }
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $email = $credentials['email'];
        return $userProvider->loadUserByUsername($email);
    }
    public function checkCredentials($credentials, UserInterface $user)
    {
        $plainPassword = $credentials['password'];
        if ($this->encoder->isPasswordValid($user, $plainPassword)) {

            if($user->getEnabled()){
                return true;
            } else {
                throw new AuthenticationException('User are disabled');
            }
        } else {
            throw new BadCredentialsException();
        }
    }
    protected function getLoginUrl()
    {
        return $this->router->generate('user_security_login');
    }
    protected function getDefaultSuccessRedirectUrl()
    {
        return $this->router->generate('homepage');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        /** @var \UserBundle\Entity\User $user */
        $user = $token->getUser();

        $user->setLastConnexion(new \DateTime());
        $this->entityManager->flush();

//        if(null === $user->getFirstConnection()){
//            $user->setFirstConnection(new \DateTime('now'));
//            $this->session->set('user_first_connection/email', $user->getEmail());
//            $this->entityManager->flush();
//        }

        return new RedirectResponse($this->getDefaultSuccessRedirectUrl());
    }
}