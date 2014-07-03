<?php

namespace Stream;

use Stream\Exceptions\ConnectionStreamException;
use Stream\Exceptions\NotStringStreamException;
use Stream\Exceptions\PortValidateStreamException;
use Stream\Exceptions\ProtocolValidateStreamException;
use Stream\Exceptions\StreamException;

/**
 * @author KonstantinKuklin <konstantin.kuklin@gmail.com>
 */
class Stream
{
    private $stream = null;
    private $driver = null;

    private $path = null;
    private $protocol = null;
    private $port = 0;

    private $readTimeOutSeconds = 0;
    private $readTimeOutMicroSeconds = 0;

    const PROTOCOL_TCP = 'tcp';
    const PROTOCOL_UDP = 'udp';
    const PROTOCOL_UNIX = 'unix';

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
        // $this->validatePath($path);
        $this->validateProtocol($protocol);
        // it is doesn't matter what is the port if protocol is UNIX
        if ($protocol !== self::PROTOCOL_UNIX) {
            $this->validatePort($port);
        }

        if ($driver !== null) {
            $this->driver = $driver;
        }
        $this->path = $path;
        $this->port = $port;
        $this->protocol = $protocol;
    }


    /**
     * @return bool
     * @throws Exceptions\ConnectionStreamException
     */
    public function open()
    {
        $stream = stream_socket_client($this->getUrlConnection(), $errorNumber, $errorMessage);
        if (!$stream) {
            throw new ConnectionStreamException(
                sprintf(
                    "Can't open '%s'. Error number: '%d', error message: '%s'",
                    $this->getUrlConnection(),
                    $errorNumber,
                    $errorMessage
                )

            );
        }

        $this->stream = $stream;

        return true;
    }

    /**
     * @throws Exceptions\StreamException
     * @return bool
     */
    public function isReadyForReading()
    {
        $read = array($this->getStream());
        $write = null;
        $except = null;

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
     * @throws Exceptions\StreamException
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
     * @throws Exceptions\StreamException
     */
    public function setTimeOut($seconds)
    {
        if (!is_int($seconds) && $seconds < 0) {
            throw new StreamException(
                sprintf("Seconds must be int >= 0, got %s with value %s.", gettype($seconds), $seconds)
            );
        }
        $this->isOpened(true);
        stream_set_timeout($this->getStream(), $seconds);
    }

    /**
     * @param bool $blocking
     *
     * @throws Exceptions\StreamException
     */
    private function setBlocking($blocking)
    {
        if (!is_bool($blocking)) {
            throw new StreamException(
                sprintf("Must be boolean, got %s with value %s.", gettype($blocking), $blocking)
            );
        }
        $this->isOpened(true);
        stream_set_blocking($this->getStream(), $blocking);
    }

    /**
     * @throws Exceptions\StreamException
     */
    public function setBlockingOn()
    {
        $this->setBlocking(true);
    }

    /**
     * @throws Exceptions\StreamException
     */
    public function setBlockingOff()
    {
        $this->setBlocking(false);
    }

    /**
     * @param int $seconds
     * @param int $microSeconds
     *
     * @throws Exceptions\StreamException
     */
    public function setReadTimeOut($seconds, $microSeconds = 0)
    {
        if (!is_int($seconds) && $seconds < 0) {
            throw new StreamException(
                sprintf("Seconds must be int >= 0, got %s with value %s.", gettype($seconds), $seconds)
            );
        }

        if (!is_int($microSeconds) && $microSeconds < 0) {
            throw new StreamException(
                sprintf("Micro seconds must be int >= 0, got %s with value %s.", gettype($seconds), $seconds)
            );
        }

        $this->isOpened(true);

        $this->readTimeOutSeconds = $seconds;
        $this->readTimeOutMicroSeconds = $microSeconds;
    }

    /**
     * @param int $maxLength
     *
     * @return string
     * @throws Exceptions\StreamException
     */
    public function getContentsByFread($maxLength)
    {
        $this->validateInt($maxLength, "Mus be int > 0");
        $this->prepareGetContents();

        return $this->afterGetContents(@fread($this->getStream(), $maxLength));
    }

    /**
     * @param null|int $length
     *
     * @return string
     * @throws Exceptions\StreamException
     */
    public function getContentsByFgets($length = null)
    {
        $this->validateInt($length, "Mus be int > 0");
        $this->prepareGetContents();

        return $this->afterGetContents(@fgets($this->getStream(), $length));
    }

    /**
     * @return string
     * @throws Exceptions\StreamException
     */
    public function getContentsByFgetc()
    {
        $this->prepareGetContents();

        return $this->afterGetContents(@fgetc($this->getStream()));
    }

    /**
     * @param int $maxLength
     * @param int $offset
     *
     * @return string
     */
    public function getContentsBySteamGetContents($maxLength = 1024, $offset = -1)
    {
        $this->prepareGetContents();

        return $this->afterGetContents(@stream_get_contents($this->getStream(), $maxLength, $offset));
    }

    /**
     * @param int  $length
     * @param null $ending
     *
     * @return string
     */
    public function getContentsBySteamGetLine($length, $ending = null)
    {
        $this->prepareGetContents();

        return $this->afterGetContents(@stream_get_line($this->getStream(), $length, $ending));
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
        if (!$this->isOpened()) {
            $this->open();
        }

        if ($this->hasDriver()) {
            $contents = $this->getDriver()->prepareSendData($contents);
        }

        if (!is_string($contents)) {
            throw new NotStringStreamException(
                sprintf("Can't sent not a string data.Data sent: %s", print_r($contents, true))
            );
        }

        $bytesSent = stream_socket_sendto($this->getStream(), $contents);
        if (!$bytesSent) {
            throw new StreamException(
                sprintf("Can't sent contents to '%s'", $this->getUrlConnection())
            );
        }

        return $bytesSent;
    }

    /**
     * @throws Exceptions\StreamException
     */
    public function close()
    {
        if ($this->getStream() !== null) {
            if (!fclose($this->getStream())) {
                throw new StreamException(
                    sprintf("Can't close connection to '%s'", $this->getUrlConnection())
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
            return feof($this->getStream());
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
     * @return string
     */
    private function getUrlConnection()
    {
        $urlConnection = $this->getProtocol() . '://' . $this->getPath();
        if ($this->getPort() > 0) {
            $urlConnection .= ':' . $this->getPort();
        }

        return $urlConnection;
    }

    /**
     * @return null|string
     */
    private function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @return int
     */
    private function getPort()
    {
        return $this->port;
    }

    /**
     * @return null|string
     */
    private function getPath()
    {
        return $this->path;
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
     * Returns the value of the driver is installed
     *
     * @return bool
     *         true - we have a driver
     *         false - we don't have a driver
     */
    private function hasDriver()
    {
        if ($this->getDriver() !== null) {
            return true;
        }

        return false;
    }

    /**
     * Method return Driver for worked with stream.
     * This driver will make changes with receive and send data.
     *
     * @return StreamDriverInterface
     */
    private function getDriver()
    {
        return $this->driver;
    }

//    /**
//     * @param string $path
//     *
//     * @return bool
//     */
//    private function validatePath($path)
//    {
//        // TODO. Not yet work
//        return true;
//    }

    /**
     * Check that port is integer and the value is inside 0-65535
     *
     * @param  int|null $port
     *         Integer value of port
     *
     * @throws PortValidateStreamException
     * @return bool
     *         Return true if all ok
     */
    private function validatePort($port)
    {
        if (is_int($port) && $port > 0 && $port < 65535) {
            return true;
        }

        throw new PortValidateStreamException(
            sprintf("Port '%s' is not a integer number or not inside the range: 0-65535", $port)
        );
    }

    /**
     * Try to understand what 1 of the 3 known protocol we shall use
     *
     * @param  string $protocol
     *         String value of protocol type
     *
     * @return bool
     *         Return true if all ok
     * @throws ProtocolValidateStreamException
     *         Throw exception if protocol undefined
     */
    private function validateProtocol($protocol)
    {
        if ($protocol == self::PROTOCOL_TCP) {
            return true;
        } elseif ($protocol == self::PROTOCOL_UDP) {
            return true;
        } elseif ($protocol == self::PROTOCOL_UNIX) {
            return true;
        } else {
            throw new ProtocolValidateStreamException(
                sprintf("Protocol '%s' unidentified", $protocol)
            );
        }

    }

    /**
     * @param int         $int
     * @param null|string $throwWithMessage
     *
     * @return bool
     * @throws Exceptions\StreamException
     */
    private function validateInt($int, $throwWithMessage = null)
    {
        if (!is_int($int)) {
            if (!is_null($throwWithMessage)) {
                throw new StreamException(
                    sprintf("%s.But got %s with value %s.", $throwWithMessage, gettype($int), $int)
                );
            }

            return false;
        }

        return true;
    }

    /**
     * @throws Exceptions\ConnectionStreamException
     * @throws Exceptions\StreamException
     */
    private function prepareGetContents()
    {
        if (!$this->isOpened()) {
            $this->open();
        }
    }

    /**
     * @param string $receiveMessage
     *
     * @throws Exceptions\StreamException
     * @return mixed
     */
    private function afterGetContents($receiveMessage)
    {
        if ($this->hasDriver()) {
            $receiveMessage = $this->getDriver()->prepareReceiveData($receiveMessage);
        }

        return $receiveMessage;
    }
}
