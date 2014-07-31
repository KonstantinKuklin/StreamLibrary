<?php
/**
 * @author KonstantinKuklin <konstantin.kuklin@gmail.com>
 */

namespace Stream\ReceiveMethod;


class FreadMethod extends AbstractMethod
{
    /** @var int */
    private $maxLength = 0;

    /**
     * @param int $maxLength
     */
    public function __construct($maxLength)
    {
        $this->validateInt($maxLength, 0);
        $this->maxLength = $maxLength;
    }

    /**
     * @param resource $streamResource
     *
     * @return string
     */
    public function readStream($streamResource)
    {
        return @fread($streamResource, $this->maxLength);
    }
} 