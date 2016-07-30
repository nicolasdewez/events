<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Event;
use AppBundle\Form\Type\EventType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/event")
 */
class EventController extends Controller
{
    /**
     * @return Response
     *
     * @Route("", name="app_events", methods={"GET"})
     */
    public function eventsAction()
    {
        $events = $this->getDoctrine()->getRepository(Event::class)->findBy([], ['active' => 'DESC', 'code' => 'ASC']);

        return $this->render('event/list.html.twig', ['events' => $events]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/add", name="app_events_add", methods={"GET", "POST"})
     */
    public function addEventAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->add('submit', SubmitType::class, ['label' => 'form.save']);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->persist($event);
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', 'notice.event_added');

            return $this->redirectToRoute('app_events');
        }

        return $this->render('event/form.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @param Request $request
     * @param Event $event
     *
     * @return Response
     *
     * @Route("/{id}", name="app_events_edit", methods={"GET", "POST"}, requirements={"id": "^\d+$"})
     */
    public function editEventAction(Request $request, Event $event)
    {
        $form = $this->createForm(EventType::class, $event);
        $form->add('submit', SubmitType::class, ['label' => 'form.save']);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->get('doctrine.orm.entity_manager')->flush();
            $this->get('session')->getFlashBag()->add('notice', 'notice.event_updated');

            return $this->redirectToRoute('app_events');
        }

        return $this->render('event/form.html.twig', ['form' => $form->createView(), 'event' => $event]);
    }
}
