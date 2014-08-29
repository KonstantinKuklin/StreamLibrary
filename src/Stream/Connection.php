<?php
/**
 * @author KonstantinKuklin <konstantin.kuklin@gmail.com>
 */

namespace Stream;

class Connection
{
    const PROTOCOL_TCP = 'tcp';
    const PROTOCOL_UDP = 'udp';
    const PROTOCOL_UNIX = 'unix';

    private $path = null;
    private $protocol = null;
    private $port = 0;

    /**
     * @param string $path
     * @param string $protocol
     * @param int    $port
     *
     * @throws Exception\PathValidateStreamException
     * @throws Exception\PortValidateStreamException
     * @throws Exception\ProtocolValidateStreamException
     */
    public function __construct($path, $protocol, $port = 0)
    {
        // $this->validatePath($path);
        Validator::validateProtocol($protocol);
        // it is doesn't matter what is the port if protocol is UNIX
        if ($protocol !== self::PROTOCOL_UNIX) {
            Validator::validatePort($port);
        }
        Validator::validatePath($path);

        $this->port = $port;
        $this->path = $path;
        $this->protocol = $protocol;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return null|string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getUrlConnection()
    {
        $urlConnection = $this->getProtocol() . '://' . $this->getPath();
        if ($this->getPort() > 0) {
            $urlConnection .= ':' . $this->getPort();
        }

        return $urlConnection;
    }
} 