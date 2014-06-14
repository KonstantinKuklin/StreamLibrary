<?php

namespace Stream;

/**
 * @author KonstantinKuklin <konstantin.kuklin@gmail.com>
 */
interface StreamDriverInterface
{
    /**
     * @param  string $data
     * @return string
     */
    public function prepareSendData($data);

    /**
     * @param  string $data
     * @return string
     */
    public function prepareReceiveData($data);

}