<?php
/**
 * @author KonstantinKuklin <konstantin.kuklin@gmail.com>
 */

namespace Stream\ReceiveMethod;


class StreamGetContentsMethod extends AbstractMethod
{
    /** @var int */
    private $maxLength;

    /** @var int */
    private $offset;

    /**
     * @param int $maxLength
     * @param int $offset
     */
    public function __construct($maxLength = 1024, $offset = -1)
    {
        if ($maxLength !== 1024) {
            $this->validateInt($maxLength, 0);
        }
        // if offset was set it must be >= 0
        if ($offset !== -1) {
            $this->validateInt($offset, 0, null, true);
        }
        
        $this->maxLength = $maxLength;
        $this->offset = $offset;
    }

    /**
     * @param resource $streamResource
     *
     * @return string
     */
    public function readStream($streamResource)
    {
        return @stream_get_contents($streamResource, $this->maxLength, $this->offset);
    }
} 