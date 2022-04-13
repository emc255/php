<?php

class CustomError extends Exception
{
  private $errorStatus;
  private $errorMessage;

  function __construct($error, $errorMessage)
  {
    $this->errorStatus = $error;
    $this->errorMessage = $errorMessage;
  }


  function getErrorStatus()
  {
    return $this->errorStatus;
  }

  function getErrorMessage()
  {
    return $this->errorMessage;
  }
}
