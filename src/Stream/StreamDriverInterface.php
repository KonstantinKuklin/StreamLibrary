<?php

namespace Stream;

/**
 * @author KonstantinKuklin <konstantin.kuklin@gmail.com>
 */
interface StreamDriverInterface
{
    /**
     * @param  array $data
     * @return string
     */
    public function prepareSendData($data);

    /**
     * @param  string $data
     * @return array
     */
    public function prepareReceiveData($data);

}