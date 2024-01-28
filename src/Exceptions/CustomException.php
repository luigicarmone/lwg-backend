<?php

namespace App\Exceptions;

use App\Model\Response400;

class CustomException extends \Exception
{
    private $errorMessage;

    public function __construct($message = "C'è stato un errore, contattare il supporto BeKube", $code = 400, $errorMessage = null, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->errorMessage = $errorMessage ?? Response400::$DEFAULT_GENERIC_ERROR;
    }

    public function getErrorMessage() {
        return $this->errorMessage;
    }
}