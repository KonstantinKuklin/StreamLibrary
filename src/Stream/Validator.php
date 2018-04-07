<?php
/**
 * @author KonstantinKuklin <konstantin.kuklin@gmail.com>
 */

namespace Stream;

use Respect\Validation\Validator as v;
use Stream\Exception\PathValidateStreamException;
use Stream\Exception\PortValidateStreamException;
use Stream\Exception\ProtocolValidateStreamException;
use Stream\Exception\StreamException;

class Validator
{
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
    public static function validateProtocol($protocol)
    {
        if ($protocol == Connection::PROTOCOL_TCP) {
            return true;
        } elseif ($protocol == Connection::PROTOCOL_UDP) {
            return true;
        } elseif ($protocol == Connection::PROTOCOL_UNIX) {
            return true;
        } else {
            throw new ProtocolValidateStreamException(
                sprintf("Protocol '%s' unidentified", $protocol)
            );
        }
    }

    /**
     * @param $port
     *
     * @throws PortValidateStreamException
     */
    public static function validatePort($port)
    {
        if (!v::intVal()->between(1, 65635)->validate($port)) {
            throw new PortValidateStreamException(
                sprintf("Port '%s' is not a integer number or not inside the range: 1-65535", $port)
            );
        }
    }

    /**
     * @param string $path
     *
     * @throws PathValidateStreamException
     */
    public static function validatePath($path)
    {
        if (!is_string($path) || strlen($path) < 1) {
            throw new PathValidateStreamException("Path must be a string with length > 0");
        }
    }

    /**
     * @param int $seconds
     *
     * @throws StreamException
     */
    public static function validateSeconds($seconds)
    {
        if (!v::intVal()->min(0, true)->validate($seconds)) {
            throw new StreamException(
                sprintf("Seconds must be int >= 0, got %s with value %s.", gettype($seconds), $seconds)
            );
        }
    }

    /**
     * @param int $microSeconds
     *
     * @throws StreamException
     */
    public static function validateMicroSeconds($microSeconds)
    {
        if ($microSeconds !== 0 && !v::intVal()->min(0, true)->validate($microSeconds)) {
            throw new StreamException(
                sprintf("Micro seconds must be int >= 0, got %s with value %s.", gettype($microSeconds), $microSeconds)
            );
        }
    }
} 
