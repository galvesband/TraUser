<?php

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
            /**
             * @var $user User
             */
            $user = $repository->createQueryBuilder('u')
                ->where('u.name = :user_name')
                ->andWhere('u.email = :email')
                ->andWhere('u.isActive = 1')
                ->setParameter('user_name', $request->get('username'))
                ->setParameter('email', $request->get('email'))
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();

            if ($user)
            {
                $token = $user->getToken();
                $em = $this->get('doctrine')->getManager();
                if ($token) {
                    $token->setCreatedAt(new \DateTime('now'));
                    $token->setToken();
                } else {
                    $token = new ResetToken();
                    $token->setUser($user);
                    $em->persist($token);
                }
                $em->flush();

                $message = \Swift_Message::newInstance()
                    ->setFrom('galvesband@gmail.com')
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
            //return $this->redirectToRoute('login');
        }

        return $this->render('@GalvesbandTraUser/security/forgot_password.html.twig');
    }
}