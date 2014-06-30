<?php
/**
 * @author KonstantinKuklin <konstantin.kuklin@gmail.com>
 */

namespace Stream\Tests;


use Stream\Stream;
use Stream\Exceptions\NotStringStreamException;
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
        $stream = new Stream('google.ru', Stream::PROTOCOL_TCP, 80);

        try {
            $stream->sendContents((array)$this->query);
        } catch (NotStringStreamException $e) {
            return;
        }

        try {
            unset($stream);
        } catch (\Exception $e) {
            // problem with good connection closing google.
            // some times google close it, some times not, but it is don't care in this test
        }

        $this->fail("sendContents with array in parameter didn't return NotStringStreamException exception.");
    }

    public function testSendContentsWithDriver()
    {
        $stream = new Stream('google.ru', Stream::PROTOCOL_TCP, 80, new TestDriver());

        try {
            $stream->sendContents((array)$this->query);
        } catch (NotStringStreamException $e) {
            $this->fail("sendContents with array in parameter didn't return NotStringStreamException exception.");
        }

        try {
            unset($stream);
        } catch (\Exception $e) {
            // problem with good connection closing google.
            // some times google close it, some times not, but it is don't care in this test
        }

        $this->assertTrue(true);
    }

    public function testGetContentsWork()
    {
        $stream = new Stream('google.ru', Stream::PROTOCOL_TCP, 80);

        $stream->sendContents($this->query);
        $contents = explode("\n", $stream->getContents());
        if (!isset($contents[0])) {
            $this->fail("Got wrong response.");
        }

        try {
            unset($stream);
        } catch (\Exception $e) {
            // problem with good connection closing google.
            // some times google close it, some times not, but it is don't care in this test
        }

        $this->assertEquals("HTTP/1.0 302 Found", trim($contents[0]), "Contents was got incorrect.");
    }
}