<?php
/**
 * @author KonstantinKuklin <konstantin.kuklin@gmail.com>
 */

namespace Stream\ReceiveMethod;


class FgetcMethod extends AbstractMethod
{
    /**
     * @param resource $streamResource
     *
     * @return string
     */
    public function readStream($streamResource)
    {
        return @fgetc($streamResource);
    }
}