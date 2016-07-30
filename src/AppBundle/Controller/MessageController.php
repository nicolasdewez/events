<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Message;
use AppBundle\Exception\AppException;
use AppBundle\Exception\EventNotFoundException;
use AppBundle\Exception\WorkflowTransitionException;
use AppBundle\Form\Model\MessageSearch;
use AppBundle\Form\Type\MessageSearchType;
use AppBundle\Workflow\MessageWorkflow as Workflow;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/message")
 */
class MessageController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Route("", name="app_messages", methods={"GET", "POST"})
     */
    public function messagesAction(Request $request)
    {
        $messages = [];
        $search = new MessageSearch();
        $isSubmit = false;
        $totalPages = 0;

        $form = $this->createForm(MessageSearchType::class, $search);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $isSubmit = true;
            $totalPages = $this->get('app.manager.message')->getTotalPagesBySearch($search);
            $messages = $this->get('app.manager.message')->loadBySearch($search);
        }

        return $this->render('message/list.html.twig', [
            'form' => $form->createView(),
            'messages' => $messages,
            'search' => [
                'page' => $search->getPage(),
                'isSubmit' => $isSubmit,
                'totalPages' => $totalPages,
            ],
        ]);
    }

    /**
     * @param Message $message
     * @param Request $request
     *
     * @return Response
     *
     * @throws WorkflowTransitionException
     *
     * @Route("/{id}/resend", name="app_messages_resend", methods={"POST"})
     */
    public function resendMessageAction(Message $message, Request $request)
    {
        // Check xml http
        if (!$request->isXmlHttpRequest()) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        // Process
        try {
            $this->get('app.event.publish')->resend($message);
        } catch (EventNotFoundException $exception) {
            $this->logError($exception, $request->getContent());

            return new Response('', Response::HTTP_NOT_FOUND);
        } catch (AppException $exception) {
            $this->logError($exception, $request->getContent());

            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response('', Response::HTTP_OK);
    }

    /**
     * @param Message $message
     * @param Request $request
     *
     * @return Response
     *
     * @throws WorkflowTransitionException
     *
     * @Route("/{id}/resend-missing", name="app_messages_resend_missing", methods={"POST"})
     */
    public function resendMissingMessageAction(Message $message, Request $request)
    {
        // Check xml http
        if (!$request->isXmlHttpRequest()) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        // Process
        try {
            $this->get('app.event.publish')->resendMissing($message);
        } catch (EventNotFoundException $exception) {
            $this->logError($exception, $request->getContent());

            return new Response('', Response::HTTP_NOT_FOUND);
        } catch (AppException $exception) {
            $this->logError($exception, $request->getContent());

            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response('', Response::HTTP_OK);
    }

    /**
     * @param AppException $exception
     * @param string       $message
     */
    private function logError(AppException $exception, $message)
    {
        $logger = $this->get('monolog.logger.send');

        $logger->error(
            sprintf('Error while resending event : %s', $exception->getMessage()),
            ['message' => $message]
        );
    }
}
