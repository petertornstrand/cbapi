<?php

namespace App\Exception;

class MissingCredentialsException extends \Exception {

    protected $message = 'Missing credentials';

    public function __construct(string $message = '', int $code = 0, Throwable|null $previous = null) {
        $this->message = !empty($message) ? $message : $this->message;
        parent::__construct($this->message, $code, $previous);
    }

}

