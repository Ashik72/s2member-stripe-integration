<?php

class Stripe_List2 extends Stripe_Object2
{
  public function all($params=null)
  {
    $requestor = new Stripe_ApiRequestor2($this->_apiKey);
    list($response, $apiKey) = $requestor->request(
        'get',
        $this['url'],
        $params
    );
    return Stripe_Util2::convertToStripeObject($response, $apiKey);
  }

  public function create($params=null)
  {
    $requestor = new Stripe_ApiRequestor2($this->_apiKey);
    list($response, $apiKey) = $requestor->request(
        'post', $this['url'], $params
    );
    return Stripe_Util2::convertToStripeObject($response, $apiKey);
  }

  public function retrieve($id, $params=null)
  {
    $requestor = new Stripe_ApiRequestor2($this->_apiKey);
    $base = $this['url'];
    $id = Stripe_ApiRequestor2::utf8($id);
    $extn = urlencode($id);
    list($response, $apiKey) = $requestor->request(
        'get', "$base/$extn", $params
    );
    return Stripe_Util2::convertToStripeObject($response, $apiKey);
  }

}
