<?php

namespace NacAL\Bounce\Exceptions;

use Exception;
use Log;

class AppException extends Exception
{
    public function report()
    {
        Log::channel('debug')->debug($this->message);
    }
}
