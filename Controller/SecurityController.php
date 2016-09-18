<?php

namespace Galvesband\TraUserBundle\Controller;

use Galvesband\TraUserBundle\Entity\ResetToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Galvesband\TraUserBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
                    $user->setToken($token);
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

            $fromDateTime = new \DateTime('-1 day');
            $toDateTime = new \DateTime('now');

            /**
             * @var $user User
             */
            $user = $repository->createQueryBuilder('u')
                ->innerJoin('u.token', 't')
                ->where('u.name = :user_name')
                ->andWhere('u.isActive = 1')
                ->andWhere('t.createdAt > :from_datetime')
                ->andWhere('t.createdAt < :to_datetime')
                ->andWhere('t.token = :token')
                ->setParameters([
                    'user_name' => $request->get('name'),
                    'from_datetime' => $fromDateTime,
                    'to_datetime' => $toDateTime,
                    'token' => $request->get('token')
                ])
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();

            if (!$user) {
                throw new NotFoundHttpException('No valid token was found.');
            }

            $em = $this->get('doctrine')->getManager();
            // ok, we have an user with an active token
            // First invalidate the token by just removing it
            $em->remove($user->getToken());
            $user->setToken(null);

            // Now we need to generate a random password of about 10 characters
            $newPassword = substr(bin2hex(random_bytes(32)), 0, 10);
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