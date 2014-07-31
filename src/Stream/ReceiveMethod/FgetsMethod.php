<?php
/**
 * @author KonstantinKuklin <konstantin.kuklin@gmail.com>
 */

namespace Stream\ReceiveMethod;


class FgetsMethod extends AbstractMethod
{
    /** @var int|null */
    private $length = null;

    /**
     * @param int|null $length
     */
    public function __construct($length = null)
    {
        // if not a null we need to check it
        if ($length !== null) {
            $this->validateInt($length, 0);
        }
        $this->length = $length;
    }

    /**
     * @param resource $streamResource
     *
     * @return string
     */
    public function readStream($streamResource)
    {
        return @fgets($streamResource, $this->length);
    }
} 