<?php

namespace Galvesband\TraUserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class SecurityController
 *
 * @package Galvesband\TraUserBundle\Controller
 */
class SecurityController extends Controller {
    /**
     * @Route("/login", name="login")
     * @Route("/login_check", name="login_check")
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
}