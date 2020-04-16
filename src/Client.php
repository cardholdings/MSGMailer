<?php

namespace CH\MSGMailer;

class Client
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
}
