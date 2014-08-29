<?php

namespace Stream;

use Respect\Validation\Validator as v;
use Stream\Exception\ConnectionStreamException;
use Stream\Exception\PortValidateStreamException;
use Stream\Exception\ProtocolValidateStreamException;
use Stream\Exception\ReadStreamException;
use Stream\Exception\ReceiveMethodStreamException;
use Stream\Exception\StreamException;
use Stream\ReceiveMethod\MethodInterface;

/**
 * @author KonstantinKuklin <konstantin.kuklin@gmail.com>
 */
class Stream
{
    private $stream = null;
    private $connection = null;
    private $dataHandler = null;

    private $readTimeOutSeconds = 0;
    private $readTimeOutMicroSeconds = 0;

    private $method = null;


    const STR_EMPTY = '';

    /**
     * @param  string                $path
     *         Path to file on system or ip address in network or hostname
     * @param  string                $protocol
     *         String value of protocol type
     * @param  int                   $port
     *         Integer value of port
     * @param  StreamDriverInterface $driver
     *         Driver object that will make changes on send and receive data via stream
     *
     * @throws PortValidateStreamException
     *         Throw if port is not valid
     * @throws ProtocolValidateStreamException
     *         Throw if protocol is not valid
     */
    public function __construct($path, $protocol, $port = 0, StreamDriverInterface $driver = null)
    {
        if ($driver !== null) {
            $this->driver = $driver;
        }

        $this->connection = new Connection($path, $protocol, $port);
        $this->dataHandler = new DataHandler($driver);
    }


    /**
     * @throws ConnectionStreamException
     * @throws StreamException
     * @return boolean
     */
    public function open()
    {
        if (!$this->isOpened()) {
            $stream = @stream_socket_client($this->getConnection()->getUrlConnection(), $errorNumber, $errorMessage);
            if (!$stream) {
                throw new ConnectionStreamException(
                    sprintf(
                        "Can't open '%s'. Error number: '%d', error message: '%s'",
                        $this->getConnection()->getUrlConnection(),
                        $errorNumber,
                        $errorMessage
                    )

                );
            }

            $this->stream = $stream;
        }

        return true;
    }

    /**
     * @throws Exception\StreamException
     * @return bool
     */
    public function isReadyForReading()
    {
        $read = array($this->getStream());
        $write = array();
        $except = array();

        if (false === ($countChanged = stream_select(
                $read,
                $write,
                $except,
                $this->readTimeOutSeconds,
                $this->readTimeOutMicroSeconds
            ))
        ) {
            // error
            throw new StreamException(
                sprintf(
                    'Error stream_select with time delay %d seconds and %d microseconds.',
                    $this->readTimeOutSeconds,
                    $this->readTimeOutMicroSeconds
                )
            );
        } else {
            if ($countChanged > 0) {
                // stream was updated
                return true;
            }
        }

        return false;
    }

    /**
     * @param bool $exceptionThrow
     *
     * @throws Exception\StreamException
     * @return bool
     */
    public function isOpened($exceptionThrow = false)
    {
        $isStreamAlive = !is_null($this->getStream());
        if ($exceptionThrow && !$isStreamAlive) {
            throw new StreamException("Stream is not opened.");
        }

        return $isStreamAlive;
    }

    /**
     * @param int $seconds
     *
     * @throws Exception\StreamException
     */
    public function setTimeOut($seconds)
    {
        Validator::validateSeconds($seconds);
        $this->isOpened(true);
        @stream_set_timeout($this->getStream(), $seconds);
    }

    /**
     * @throws Exception\StreamException
     */
    public function setBlockingOn()
    {
        $this->setBlocking(true);
    }

    /**
     * @throws Exception\StreamException
     */
    public function setBlockingOff()
    {
        $this->setBlocking(false);
    }

    /**
     * @param int $seconds
     * @param int $microSeconds
     *
     * @throws Exception\StreamException
     */
    public function setReadTimeOut($seconds, $microSeconds = 0)
    {
        Validator::validateSeconds($seconds);
        Validator::validateMicroSeconds($microSeconds);

        $this->isOpened(true);

        $this->readTimeOutSeconds = $seconds;
        $this->readTimeOutMicroSeconds = $microSeconds;
    }

    /**
     * @param MethodInterface $method
     */
    public function setReceiveMethod(MethodInterface $method)
    {
        $this->method = $method;
    }

    /**
     * @return null|string
     */
    public function getReceiveMethodName()
    {
        if ($this->getReceiveMethod() === null) {
            return null;
        } else {
            return get_class($this->getReceiveMethod());
        }
    }

    /**
     * @throws ReceiveMethodStreamException
     */
    private function checkReceiveMethod()
    {
        if ($this->getReceiveMethod() === null) {
            throw new ReceiveMethodStreamException(
                'ReceiveMethod was not set. Use $stream->setReceiveMethod to fix it.'
            );
        }
    }

    /**
     * @throws Exception\ConnectionStreamException
     * @throws Exception\ReadStreamException
     * @throws Exception\ReceiveMethodStreamException
     * @throws Exception\StreamException
     * @return string
     */
    public function getContents()
    {
        $this->checkReceiveMethod();
        $this->open();

        $receiveMessage = $this->getReceiveMethod()->readStream($this->getExistedStream());

        if (!$receiveMessage) {
            throw new ReadStreamException("Nothing was readed from stream.");
        }

        return $this->getDataHandler()->prepareReceiveData($receiveMessage);
    }

    /**
     * @return array|bool
     * @throws Exception\StreamException
     */
    public function getMetaData()
    {
        if (!$this->isOpened()) {
            return false;
        }

        return @stream_get_meta_data($this->getStream());
    }

    /**
     * Send data throw active stream
     *
     * @param  string $contents
     *         Data for sending
     *
     * @return int
     *         Count of bytes sent successfully
     * @throws StreamException
     *         Throw exception if send process failure or count of bytes
     *         sent successfully is 0
     */
    public function sendContents($contents)
    {
        $this->open();
        $contents = $this->getDataHandler()->prepareSendData($contents);

        $bytesSent = stream_socket_sendto($this->getExistedStream(), $contents);

        if (!$bytesSent) {
            throw new StreamException(
                sprintf("Can't sent contents to '%s'", $this->getConnection()->getUrlConnection())
            );
        }

        return $bytesSent;
    }

    /**
     * @throws Exception\StreamException
     */
    public function close()
    {
        $stream = $this->getStream();
        if ($stream !== null) {
            if (!fclose($stream)) {
                throw new StreamException(
                    sprintf("Can't close connection to '%s'", $this->getConnection()->getUrlConnection())
                );
            }
        }

        $this->stream = null;
    }

    /**
     * @return bool
     */
    public function isEof()
    {
        if ($this->getStream() === null) {
            return true;
        } else {
            return @feof($this->getStream());
        }
    }

    /**
     * Close connection on destructing
     *
     * @throws StreamException
     *         Throw exception if connection was closed with errors
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }
    
    /**
     * Get the resource link or null if we don't have active stream
     *
     * @return null|resource
     *         null - we don't have the resource
     *         resource - we have active resource
     */
    private function getStream()
    {
        return $this->stream;
    }

    /**
     * @return resource
     * @throws StreamException
     */
    private function getExistedStream()
    {
        if ($this->stream !== null) {
            return $this->stream;
        }

        throw new StreamException("Stream is null");
    }

    /**
     * @param bool $blocking
     *
     * @throws Exception\StreamException
     */
    private function setBlocking($blocking)
    {
        if (!is_bool($blocking)) {
            throw new StreamException(
                sprintf("Must be boolean, got %s with value %s.", gettype($blocking), $blocking)
            );
        }
        $this->isOpened(true);
        @stream_set_blocking($this->getStream(), $blocking);
    }

    /**
     * @return MethodInterface
     */
    private function getReceiveMethod()
    {
        return $this->method;
    }

    /**
     * @return DataHandler
     */
    private function getDataHandler()
    {
        return $this->dataHandler;
    }
}
