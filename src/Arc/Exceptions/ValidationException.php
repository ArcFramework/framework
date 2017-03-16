<?php

namespace Arc\Exceptions;

class ValidationException extends \Exception
{
    public function setErrors($errors)
    {
        $this->errors = $errors;
        return $this;
    }

    public function errors()
    {
        return $this->errors;
    }
}
