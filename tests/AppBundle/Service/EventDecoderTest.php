<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity\Message;
use AppBundle\Service\EventDecoder;
use JMS\Serializer\SerializerInterface;
use Ndewez\EventsBundle\Model\Event;
use Psr\Log\NullLogger;

/**
 * Class EventDecoderTest.
 */
class EventDecoderTest extends \PHPUnit_Framework_TestCase
{
    public function testDecodeByString()
    {
        $serializer = $this->getSerializerMock();
        $event = new Event();
        $event
            ->setTitle('myEvent')
            ->setPayload('Ndewez\\Model\\MyObject')
            ->setNamespace('{\"title\":\"coucou\"}')
        ;

        $serializer->method('deserialize')
            ->willReturn($event)
        ;

        $decoder = new EventDecoder($serializer, new NullLogger());
        $event = $decoder->decodeByString('{"namespace": "Ndewez\\Model\\MyObject", "title": "myEvent", "payload": "{\"title\":\"coucou\"}"}');

        $this->assertNotNull($event);
    }

    /**
     * @expectedException \AppBundle\Exception\BadEventException
     */
    public function testBadDecodeByString()
    {
        $serializer = $this->getSerializerMock();
        $serializer->method('deserialize')
            ->willReturn(new Event())
        ;

        $decoder = new EventDecoder($serializer, new NullLogger());
        $decoder->decodeByString('{"namespace": "Ndewez\\Model\\MyObject", "title": "myEvent", "payload": "{\"title\":\"coucou\"}"}');
    }

    public function testDecodeByMessage()
    {
        $expected = new Event();
        $expected
            ->setTitle('title')
            ->setNamespace('namespace')
            ->setPayload('payload')
        ;

        $message = new Message();
        $message
            ->setTitle('title')
            ->setNamespace('namespace')
            ->setPayload('payload')
         ;

        $decoder = new EventDecoder($this->getSerializerMock(), new NullLogger());
        $event = $decoder->decodeByMessage($message);

        $this->assertNotNull($event);
        $this->assertEquals($expected, $event);
    }

    /**
     * @expectedException \AppBundle\Exception\BadEventException
     */
    public function testBadDecodeByMessage()
    {
        $message = new Message();
        $message
            ->setTitle('title')
            ->setPayload('payload')
        ;

        $decoder = new EventDecoder($this->getSerializerMock(), new NullLogger());
        $decoder->decodeByMessage($message);
    }

    public function testEventIsInitialized()
    {
        $event = new Event();
        $event
            ->setTitle('title')
            ->setNamespace('namespace')
            ->setPayload('payload')
        ;

        $decoder = new EventDecoder($this->getSerializerMock(), new NullLogger());

        $class = new \ReflectionClass($decoder);
        $method = $class->getMethod('checkEvent');
        $method->setAccessible(true);

        $method->invokeArgs($decoder, [$event]);
    }

    /**
     * @expectedException \AppBundle\Exception\BadEventException
     */
    public function testEventIsInitializedThrowsException()
    {
        $decoder = new EventDecoder($this->getSerializerMock(), new NullLogger());

        $class = new \ReflectionClass($decoder);
        $method = $class->getMethod('checkEvent');
        $method->setAccessible(true);

        $method->invokeArgs($decoder, [new Event()]);
    }

    /**
     * @return SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getSerializerMock()
    {
        return $this->getMockBuilder('JMS\Serializer\SerializerInterface')
            ->getMock()
        ;
    }
}
