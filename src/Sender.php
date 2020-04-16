<?php

namespace CH\MSGMailer;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class Sender extends GuzzleClient
{
  //
  public function __construct($auth, $method, $url, $filters, $body, $type, array $requestOptions = [])
  {
    parent::__construct(['defaults' => [
      'headers' => [
        'user-agent' => Config::USER_AGENT . phpversion() . '/' . Client::WRAPPER_VERSION
      ]
    ]]);
    $this->type = $type;
    $this->auth = $auth;
    $this->method = $method;
    $this->url = $url;
    $this->filters = $filters;
    $this->body = $body;
    $this->requestOptions = $requestOptions;
  }
}
