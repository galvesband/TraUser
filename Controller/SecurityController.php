<?php

/*
 * This file is part of the Galvesband TraUserBundle.
 *
 * (c) Rafael Gálvez-Cañero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Galvesband\TraUserBundle\Controller;

use Galvesband\TraUserBundle\Entity\ResetToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Galvesband\TraUserBundle\Entity\User;

/**
 * Class SecurityController
 *
 * @package Galvesband\TraUserBundle\Controller
 */
class SecurityController extends Controller {
    /**
     * @Route("/login", name="login")
     * @Route("/login_check", name="login_check")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@GalvesbandTraUser/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
        // Noop
    }

    /**
     * Sends an email to the user with a link to reset its password.
     *
     * @Route("/forgot_password", name="forgot_password")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function forgotPasswordAction(Request $request)
    {
        if (   $request->getMethod() === 'POST'
            && $request->get('username', false)
            && $request->get('email', false))
        {
            $translator = $this->get('translator');
            $repository = $this->get('doctrine')->getRepository('GalvesbandTraUserBundle:User');
            $user = $repository->findActiveByNameAndEmail($request->get('username'), $request->get('email'));

            if ($user)
            {
                $token = $user->getToken();
                $em = $this->get('doctrine')->getManager();
                if ($token) {
                    $em->remove($token);
                    $user->setToken(null);
                }

                $token = new ResetToken();
                $user->setToken($token);
                $em->persist($token);

                $em->flush();

                $message = \Swift_Message::newInstance()
                    ->setFrom($this->container->getParameter('galvesband.tra_user.mail.from'))
                    ->setTo($user->getEmail())
                    ->setSubject($translator->trans('Password Recovery', [], 'GalvesbandTraUserBundle'))
                    ->setBody(
                        $this->renderView(
                            '@GalvesbandTraUser/emails/password_reset.html.twig',
                            [
                                'name' => $user->getName(),
                                'token' => $token->getToken(),
                            ]
                        )
                    )
                    ->addPart(
                        $this->renderView(
                            '@GalvesbandTraUser/emails/password_reset.txt.twig',
                            [
                                'name' => $user->getName(),
                                'token' => $token->getToken(),
                            ]
                        )
                    );
                $this->get('mailer')->send($message);
            }

            $this->get('session')->getFlashBag()->add(
                'notice',
                $translator->trans("An email has been sent to the user's account with instructions on how to reset his password.", [], 'GalvesbandTraUserBundle')
            );
            return $this->redirectToRoute('login');
        }

        return $this->render('@GalvesbandTraUser/security/forgot_password.html.twig');
    }

    /**
     * Given a token and an user name, generates a new password for the user and invalidates the token.
     *
     * @Route("/recover_password/{name}/{token}", name="recover_password", methods="GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function recoverPasswordAction(Request $request)
    {
        if ($request->get('name', false) && $request->get('token', false))
        {
            $repository = $this->get('doctrine')->getRepository('GalvesbandTraUserBundle:User');
            $user = $repository->findByToken($request->get('name'), $request->get('token'));
            if (!$user) {
                throw $this->createNotFoundException('Parameters missing');
            }

            $em = $this->get('doctrine')->getManager();
            // ok, we have an user with an active token
            // First invalidate the token by just removing it
            $em->remove($user->getToken());
            $user->setToken(null);

            // Now we need to generate a random password of about 10 characters
            $generator = $this->get('galvesband.tra.user.security.generator.factory')
                ->getMediumStrengthGenerator();
            $newPassword = $generator->generateString(10);
            $user->setPlainPassword($newPassword);

            // Save changes
            $em->flush();

            return $this->render('@GalvesbandTraUser/security/recover_password.html.twig', [
                'new_password' => $newPassword,
                'user_name' => $user->getName()
            ]);
        }

        return $this->createNotFoundException('Parameters missing');
    }
}