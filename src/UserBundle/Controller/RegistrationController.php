<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;
use UserBundle\Event\UserEvent;
use UserBundle\Form\RegistrationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class RegistrationController extends Controller
{
    /**
     * @Route("/register", name="user_register")
     * @param Request $request
     */
    public function registerAction(Request $request)
    {
        // Create a new blank user and process the form
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode the new users password
            $encoder = $this->get('security.password_encoder');
            $password = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            // Set their role
            $user->setRole('ROLE_USER');

            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $userEvent = new UserEvent($user, $request);
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch('registration.success', $userEvent);

            return $this->redirectToRoute('user_security_login');
        }
        return $this->render('UserBundle:Registration:register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/check-email", name="user_check_email")
     * Tell the user to check his email provider
     */
    public function checkEmailAction()
    {
        $em = $this->getDoctrine()->getManager();
        $email = $this->get('session')->get('user_confirmation_email/email');
        $this->get('session')->remove('user_confirmation_email/email');
        $user = $em->getRepository('UserBundle:User')->findOneBy(['email' => $email]);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->render('UserBundle:Registration:check_email.html.twig', array(
            'user' => $user,
        ));
    }

    /**
     * @Route("confirm/{token}", name="user_confirm")
     * Receive the confirmation token from user email provider, login the user
     */
    public function confirmAction(Request $request, $token)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('UserBundle:User')->findOneBy(['confirmationToken' => $token]);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);

        $em->persist($user);
        $em->flush();


        $this->get('session')->getFlashBag()->add('notice', 'Votre email a été validé, vous pouvez vous connecter avec vos identifiants :)');

        return $this->redirectToRoute('user_security_login');
    }
}