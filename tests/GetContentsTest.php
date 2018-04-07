<?php
/**
 * @author KonstantinKuklin <konstantin.kuklin@gmail.com>
 */

namespace Stream\Tests;

use Stream\Connection;
use Stream\ReceiveMethod\StreamGetContentsMethod;
use Stream\Stream;
use Stream\Exception\NotStringStreamException;
use Stream\StreamDriverInterface;

class TestDriver implements StreamDriverInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepareSendData($data)
    {
        if (is_array($data)) {
            return implode('', $data);
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function prepareReceiveData($data)
    {
        return $data;
    }

}

class GetContentsTest extends \PHPUnit_Framework_TestCase
{
    private $query = "GET / HTTP/1.0\r\nHost: www.example.com\r\nAccept: */*\r\n\r\n";

    public function testSendContentsWithOutDriverException()
    {
        $stream = new Stream('google.ru', Connection::PROTOCOL_TCP, 80);

        try {
            $stream->sendContents((array)$this->query);
        } catch (NotStringStreamException $e) {
            return;
        }

        $this->fail("sendContents with array in parameter didn't return NotStringStreamException exception.");
    }

    public function testSendContentsWithDriver()
    {
        $stream = new Stream('google.ru', Connection::PROTOCOL_TCP, 80, new TestDriver());

        try {
            $stream->sendContents((array)$this->query);
        } catch (NotStringStreamException $e) {
            $this->fail("sendContents with array in parameter didn't return NotStringStreamException exception.");
        }

        $this->assertTrue(true);
    }

    public function testGetContentsWork()
    {
        $stream = new Stream('google.ru', Connection::PROTOCOL_TCP, 80);

        $stream->sendContents($this->query);
        $stream->setReadTimeOut(2);
        $stream->setBlockingOff();
        $stream->setReceiveMethod(new StreamGetContentsMethod());

        $contents = '';
        if ($stream->isReadyForReading()) {
            $contents .= $stream->getContents();
        }

        $contents = explode("\n", $contents);
        if (!isset($contents[0])) {
            $this->fail("Got wrong response.");
        }

        $this->assertEquals("HTTP/1.0 404 Not Found", trim($contents[0]), "Contents was got incorrect.");
    }
}
