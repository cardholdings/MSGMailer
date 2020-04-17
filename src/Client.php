<?php

namespace CH\MSGMailer;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class Client extends GuzzleClient
{
  private $accessToken;

  public function __construct($access_token)
  {
    $this->accessToken = $access_token;
  }

  private function post()
  {
    // 
  }

  public function request($method, $data)
  {
    // 
    if (method_exists($this, $method)) {
      return $this->$method('Hello World');
    } else {
      return null;
    }
  }
}
