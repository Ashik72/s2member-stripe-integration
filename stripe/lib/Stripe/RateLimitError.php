<?php

class Stripe_RateLimitError2 extends Stripe_InvalidRequestError2
{
  public function __construct($message, $param, $httpStatus=null,
      $httpBody=null, $jsonBody=null
  )
  {
    parent::__construct($message, $httpStatus, $httpBody, $jsonBody);
  }
}
