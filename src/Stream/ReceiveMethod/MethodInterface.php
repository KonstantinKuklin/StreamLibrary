<?php
/**
 * @author KonstantinKuklin <konstantin.kuklin@gmail.com>
 */

namespace Stream\ReceiveMethod;

interface MethodInterface {

    /**
     * @param resource $streamResource
     *
     * @return string
     */
    public function readStream($streamResource);
} 