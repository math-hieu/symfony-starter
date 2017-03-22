<?php
namespace UserBundle\EventListener;

use AppBundle\Mailer\Mailer;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use UserBundle\Event\UserEvent;

class RegistrationListener implements EventSubscriberInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param Session $session
     * @param EntityManager $entityManager
     */
    public function __construct(Session $session, EntityManager $entityManager)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'registration.success' => 'onRegistrationSuccess',
        );
    }

    public function onRegistrationSuccess(UserEvent $event)
    {
        $this->session->getFlashBag()->set("notice", "You are now registered, please sign in!");
//        $user = $event->getUser();
//
//        $user->setEnabled(false);
//        if (null === $user->getConfirmationToken()) {
//            $user->setConfirmationToken(rtrim(strtr(base64_encode(random_bytes(30)), '+/', '-_'), '='));
//        }
//
//        $this->mailer->sendRegistrationConfirmation($user);
//
//        $this->session->set('user_confirmation_email/email', $user->getEmail());
//
//        $this->entityManager->persist($user);
//        $this->entityManager->flush();

//        $url = $this->router->generate('fos_user_registration_check_email');
//        $event->setResponse(new RedirectResponse($url));
    }
}
