<?php

namespace Stream;

/**
 * @author KonstantinKuklin <konstantin.kuklin@gmail.com>
 */
interface StreamDriverInterface
{
    public function prepareSendData(&$data);

    public function prepareReceiveData(&$data);

}