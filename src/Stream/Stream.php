<?php

namespace Stream;

use Stream\Exceptions\ConnectionStreamException;
use Stream\Exceptions\PortValidateStreamException;
use Stream\Exceptions\ProtocolValidateStreamException;
use Stream\Exceptions\StreamException;

/**
 * @author KonstantinKuklin <konstantin.kuklin@gmail.com>
 */
class Stream
{
    private $stream = null;
    private $urlConnection = null;
    private $driver = null;

    const PROTOCOL_TCP = 'tcp';
    const PROTOCOL_UDP = 'udp';
    const PROTOCOL_UNIX = 'unix';

    /**
     * @param  string $path
     *         Path to file on system or ip address in network or hostname
     * @param  string $protocol
     *         String value of protocol type
     * @param  int $port
     *         Integer value of port
     * @param  StreamDriverInterface $driver
     *         Driver object that will make changes on send and receive data via stream
     * @throws ConnectionStreamException
     *         Throw if connection failed
     * @throws PortValidateStreamException
     *         Throw if port is not valid
     * @throws ProtocolValidateStreamException
     *         Throw if protocol is not valid
     */
    public function __construct($path, $protocol, $port = 0, StreamDriverInterface $driver = null)
    {
        $this->validatePath($path);
        $this->validateProtocol($protocol);
        // it is doesn't matter what is the port if protocol is UNIX
        if ($protocol !== self::PROTOCOL_UNIX) {
            $this->validatePort($port);
        }

        $urlConnection = $protocol . '://' . $path;
        if (!is_null($port)) {
            $urlConnection .= ':' . $port;
        }

        $socket = stream_socket_client($urlConnection, $errorNumber, $errorMessage);
        if (!$socket) {
            throw new ConnectionStreamException(
                sprintf(
                    "Can't connect to '%s'. Error number: '%d', error message: '%s'",
                    $urlConnection,
                    $errorNumber,
                    $errorMessage
                )

            );
        }

        $this->stream = $socket;
        $this->urlConnection = $urlConnection;

        if ($driver !== null) {
            $this->driver = $driver;
        }
    }

    /**
     * Receive data from active stream
     *
     * @param  int $maxLength
     *         The maximum bytes to read. Defaults to -1 (read all the remaining buffer)
     * @param  int $offset
     *         Seek to the specified offset before reading. Defaults -1 (read without offset)
     * @return string
     *         Data from stream after Driver preparation.
     * @throws StreamException
     *         Throw exception if no data has been got from stream
     */
    public function getContents($maxLength = -1, $offset = -1)
    {
        $receiveMessage = stream_get_contents($this->getStream(), $maxLength, $offset);
        if (!$receiveMessage) {
            throw new StreamException(
                sprintf("Can't read contents from '%s'", $this->urlConnection)
            );
        }

        if ($this->hasDriver()) {
            $this->getDriver()->prepareReceiveData($receiveMessage);
        }

        return $receiveMessage;
    }

    /**
     * Send data throw active stream
     *
     * @param  string $contents
     *         Data for sending
     * @return int
     *         Count of bytes sent successfully
     * @throws StreamException
     *         Throw exception if send process failure or count of bytes
     *         sent successfully is 0
     */
    public function sendContents($contents)
    {
        if ($this->hasDriver()) {
            $this->getDriver()->prepareReceiveData($contents);
        }

        $bytesSent = stream_socket_sendto($this->getStream(), $contents);
        if (!$bytesSent) {
            throw new StreamException(
                sprintf("Can't sent contents to '%s'", $this->urlConnection)
            );
        }

        return $bytesSent;
    }

    /**
     * Close connection on destructing
     *
     * @throws StreamException
     *         Throw exception if connection was closed with errors
     */
    public function __destruct()
    {
        if ($this->getStream() !== null) {
            if (!stream_socket_shutdown($this->stream, STREAM_SHUT_RDWR)) {
                throw new StreamException(
                    sprintf("Can't close connection to '%s'", $this->urlConnection)
                );
            }
        }
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
     * @return null|StreamDriverInterface
     */
    private function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param $path
     * @return bool
     */
    private function validatePath($path)
    {
        // TODO. Not yet work
        return true;
    }

    /**
     * Check that port is integer and the value is inside 0-65535
     *
     * @param  int|null $port
     *         Integer value of port
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
}
