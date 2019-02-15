<?php

namespace Bokbasen\ApiClient\Exceptions;

class MissingParameterException extends \Exception
{
    public function __construct(string $parameter)
    {
        parent::__construct(sprintf('Parameter "%s" has no value.', $parameter), 503);
    }
}
