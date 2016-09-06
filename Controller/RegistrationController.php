<?php

namespace Galvesband\TraUserBundle\Controller;

use Galvesband\TraUserBundle\Form\UserType;
use Galvesband\TraUserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RegistrationController extends Controller
{

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/registration", name="user_registration")
     */
    public function registerAction(Request $request)
    {
        // Build the form
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        // Handle submission (only on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Encode password using encoder indicated by configuration
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            // Save user
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // Redirect somewhere
            // TODO Route should be a parameter or something
            return $this->redirectToRoute('welcome');
        }

        return $this->render(
            '@GalvesbandTraUser/registration/registration.html.twig',
            ['form' => $form->createView()]
        );
    }
}