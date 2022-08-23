<?php

namespace App\Srv;

class MyException extends \Exception
{
    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }
}
