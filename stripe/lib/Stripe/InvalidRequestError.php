<?php

class Stripe_InvalidRequestError2 extends Stripe_Error2
{
  public function __construct($message, $param, $httpStatus=null,
      $httpBody=null, $jsonBody=null
  )
  {
    parent::__construct($message, $httpStatus, $httpBody, $jsonBody);
    $this->param = $param;
  }
}
