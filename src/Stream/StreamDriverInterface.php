<?php

namespace Stream;

/**
 * @author KonstantinKuklin <konstantin.kuklin@gmail.com>
 */
interface StreamDriverInterface
{
    /**
     * @param  mixed $data
     *
     * @return string
     */
    public function prepareSendData($data);

    /**
     * @param  string $data
     *
     * @return mixed
     */
    public function prepareReceiveData($data);
}