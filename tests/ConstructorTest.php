<?php

namespace Stream\Tests;

use Stream\Connection;
use Stream\Stream;
use Exception;

/**
 * @author KonstantinKuklin <konstantin.kuklin@gmail.com>
 */
class ConstructorTest extends \PHPUnit_Framework_TestCase
{
    public function testMissedProtocol()
    {
        $validPath = 'path';
        $missedProtocol = 'missed';
        $validPort = 20;
        $noDriver = null;
        $this->checkAssertionToConstructor(
            'Not fail with missed protocol',
            'Stream\Exception\ProtocolValidateStreamException',
            $validPath,
            $missedProtocol,
            $validPort,
            $noDriver
        );

        $this->assertTrue(true);
    }

    public function testZeroPortTcpProtocol()
    {
        $validPath = 'path';
        $validProtocol = Connection::PROTOCOL_TCP;
        $zeroPort = 0;
        $noDriver = null;
        $this->checkAssertionToConstructor(
            'Not fail with zero port',
            'Stream\Exception\PortValidateStreamException',
            $validPath,
            $validProtocol,
            $zeroPort,
            $noDriver
        );

        $this->assertTrue(true);
    }

    private function checkAssertionToConstructor(
        $assertMessage,
        $expectedExceptionClass,
        $path,
        $protocol,
        $port,
        $driver
    ) {
        $realExceptionClass = null;
        try {
            $stream = new Stream($path, $protocol, $port, $driver);
        } catch (Exception $e) {
            $realExceptionClass = get_class($e);
            switch ($realExceptionClass) {
                case $expectedExceptionClass:
                    return true;
                    break;
            }
        }

        $this->fail(
            sprintf(
                "Path: '%s', Protocol: '%s', Port: '%s', ExpectedException: '%s', RealException: '%s'. %s",
                $path,
                $protocol,
                $port,
                $expectedExceptionClass,
                $realExceptionClass,
                $assertMessage
            )
        );
    }
}

