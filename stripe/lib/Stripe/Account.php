<?php

class Stripe_Account2 extends Stripe_SingletonApiResource2
{
  /**
    * @param string|null $apiKey
    *
    * @return Stripe_Account
    */
  public static function retrieve($apiKey=null)
  {
    $class = get_class();
    return self::_scopedSingletonRetrieve($class, $apiKey);
  }
}
