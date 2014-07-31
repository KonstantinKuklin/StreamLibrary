<?php
/**
 * @author KonstantinKuklin <konstantin.kuklin@gmail.com>
 */

namespace Stream\ReceiveMethod;

use Respect\Validation\Validator as v;
use Stream\Exceptions\StreamException;

abstract class AbstractMethod implements MethodInterface
{
    /**
     * @param int      $value
     * @param int|null $min
     * @param int|null $max
     * @param bool     $inclusive
     *
     * @return bool
     * @throws \Stream\Exceptions\StreamException
     */
    protected function validateInt($value, $min = null, $max = null, $inclusive = false)
    {
        if (!v::int()->between($min, $max, $inclusive)->validate($value)) {
            throw new StreamException(
                sprintf(
                    "The value must be Int with min value:'%s', max value:'%s'.(Inclusive:'%s'). But got %s with value %s.",
                    $min,
                    $max,
                    $inclusive,
                    gettype($value),
                    $value
                )
            );
        }

        return true;
    }

} 