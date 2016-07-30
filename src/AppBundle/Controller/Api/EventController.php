<?php

namespace AppBundle\Controller\Api;

use AppBundle\Exception\AppException;
use AppBundle\Exception\EventNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/api")
 */
class EventController extends Controller
{
    /**
     * @Route("/publish", name="app_api_event_publish", methods={"POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function publishAction(Request $request)
    {
        try {
            $this->get('app.event.publish')->send($request->getContent());
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
            sprintf('Error while sending event by Api : %s', $exception->getMessage()),
            ['message' => $message]
        );
    }
}
