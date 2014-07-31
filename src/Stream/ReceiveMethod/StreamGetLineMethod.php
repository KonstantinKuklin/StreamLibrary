<?php
/**
 * @author KonstantinKuklin <konstantin.kuklin@gmail.com>
 */

namespace Stream\ReceiveMethod;


class StreamGetLineMethod extends AbstractMethod
{
    /** @var int */
    private $length;

    /** @var null|string */
    private $ending;

    /**
     * @param int         $length
     * @param string|null $ending
     */
    public function __construct($length, $ending = null)
    {
        $this->validateInt($length, 0);

        $this->length = $length;
        $this->ending = $ending;
    }

    /**
     * @param resource $streamResource
     *
     * @return string
     */
    public function readStream($streamResource)
    {
        return @stream_get_line($streamResource, $this->length, $this->ending);
    }
} 