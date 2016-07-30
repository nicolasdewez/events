<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\LoginType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends Controller
{
    /**
     * @Route("/connection", name="app_connection")
     *
     * @return Response
     */
    public function loginAction()
    {
        $error = $this->get('security.authentication_utils')->getLastAuthenticationError();
        $form = $this->get('form.factory')->createNamed('', LoginType::class);

        return $this->render('security/login.html.twig', ['form' => $form->createView(), 'error' => $error]);
    }
}
