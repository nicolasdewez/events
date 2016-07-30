<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Application;
use AppBundle\Form\Type\ApplicationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/application")
 */
class ApplicationController extends Controller
{
    /**
     * @return Response
     *
     * @Route("", name="app_applications", methods={"GET"})
     */
    public function applicationsAction()
    {
        $applications = $this->getDoctrine()->getRepository(Application::class)->findBy([], ['active' => 'DESC', 'title' => 'ASC']);

        return $this->render('application/list.html.twig', ['applications' => $applications]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/add", name="app_applications_add", methods={"GET", "POST"})
     */
    public function addApplicationAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $application = new Application();
        $form = $this->createForm(ApplicationType::class, $application);
        $form->add('submit', SubmitType::class, ['label' => 'form.save']);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->persist($application);
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', 'notice.application_added');

            return $this->redirectToRoute('app_applications');
        }

        return $this->render('application/form.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @param Request $request
     * @param Application $application
     *
     * @return Response
     *
     * @Route("/{id}", name="app_applications_edit", methods={"GET", "POST"}, requirements={"id": "^\d+$"})
     */
    public function editApplicationAction(Request $request, Application $application)
    {
        $form = $this->createForm(ApplicationType::class, $application);
        $form->add('submit', SubmitType::class, ['label' => 'form.save']);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->get('doctrine.orm.entity_manager')->flush();
            $this->get('session')->getFlashBag()->add('notice', 'notice.application_updated');

            return $this->redirectToRoute('app_applications');
        }

        return $this->render('application/form.html.twig', ['form' => $form->createView(), 'application' => $application]);
    }
}
