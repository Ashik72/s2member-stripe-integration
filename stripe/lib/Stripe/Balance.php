<?php

class Stripe_Balance2 extends Stripe_SingletonApiResource2
{
  /**
    * @param string|null $apiKey
    *
    * @return Stripe_Balance
    */
  public static function retrieve($apiKey=null)
  {
    $class = get_class();
    return self::_scopedSingletonRetrieve($class, $apiKey);
  }
}
