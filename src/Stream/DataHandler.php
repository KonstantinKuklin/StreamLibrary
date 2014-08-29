<?php
/**
 * @author KonstantinKuklin <konstantin.kuklin@gmail.com>
 */

namespace Stream;

use Stream\Exception\NotStringStreamException;

class DataHandler
{
    private $driver = null;

    public function __construct(StreamDriverInterface $driver = null)
    {
        $this->driver = $driver;
    }

    /**
     * @param string $data
     *
     * @return mixed
     */
    public function prepareReceiveData($data)
    {
        if ($this->hasDriver()) {
            return $this->getDriver()->prepareReceiveData($data);
        }

        return $data;
    }

    /**
     * @param mixed $data
     *
     * @throws NotStringStreamException
     * @return string
     */
    public function prepareSendData($data)
    {
        if ($this->hasDriver()) {
            $data = $this->getDriver()->prepareSendData($data);
        }

        if (!is_string($data)) {
            throw new NotStringStreamException("prepareSendData method must return string.");
        }

        return $data;
    }

    /**
     * @return boolean
     */
    private function hasDriver()
    {
        return ($this->driver !== null);
    }

    /**
     * @return null|StreamDriverInterface
     */
    private function getDriver()
    {
        return $this->driver;
    }
} 