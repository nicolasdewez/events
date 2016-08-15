<?php

namespace AppBundle\Service\Connector;

use AppBundle\Exception\SendException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use JMS\Serializer\SerializerInterface;
use Ndewez\EventsBundle\Model\Event;

/**
 * Class HttpConnector.
 */
class HttpConnector implements ConnectorInterface
{
    const LISTEN_URL = '/events/listen';

    /** @var Client */
    private $client;

    /** @var SerializerInterface */
    private $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->client = new Client();
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Event $event, $path)
    {
        try {
            $this->client->post(
                $this->buildUrl($path), [
                    'body' => $this->serializer->serialize($event, 'json'),
                ]
            );
        } catch (RequestException $exception) {
            throw new SendException($exception->getMessage());
        }
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function buildUrl($path)
    {
        return sprintf('%s%s', $path, self::LISTEN_URL);
    }
}
