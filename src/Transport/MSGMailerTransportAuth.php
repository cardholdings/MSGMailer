<?php

namespace CH\MSGMailer\Transport;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use Carbon\Carbon;

/**
 * Authentication class to minimieze the calls for a new request token...
 * 
 */
trait MSGMailerTransportAuth
{

  /**
   * Puts the Request URI together and returns it.
   *
   * @return string
   */
  private function getTokenRequestUri()
  {
    return MSGMailerTransportConfig::CONN_TYPE . MSGMailerTransportConfig::TOKEN_REQ_URL .
      "/" . config('msgmailer.tenant_id') .
      "/" . MSGMailerTransportConfig::TOKEN_REQ_ENDPOINT;
  }
  /**
   * Creates the payload for the x-www-form-urlencoded body.
   *
   * @return array
   */
  private function payload()
  {
    return [
      'client_id' => config('msgmailer.client_id'),
      'scope' => config('msgmailer.scope'),
      'client_secret' => config('msgmailer.client_secret'),
      'grant_type' => config('msgmailer.grant_type')
    ];
  }
  /**
   * Sends the authentification request and returns the Bearer Key
   * sets the next expiration to prevent to many requests to get an
   * access_token.
   *
   * @return string 
   */
  public function auth()
  {
    $authed = $this->isAuthenticated();
    if ($authed) return $authed;

    $cl = new GuzzleClient();

    $res = $cl->request(
      MSGMailerTransportConfig::METHOD,
      $this->getTokenRequestUri(),
      [
        'form_params' => $this->payload(),
        'headers' => [
          'Accept'     => 'application/json',
          'Content-Type' => MSGMailerTransportConfig::CONTENT_TYPE
        ]
      ],
    );

    $status_code = $res->getStatusCode();

    if (!$status_code == 200) $this->handleErrorCodes($status_code);

    $content = json_decode($res->getBody(), true);

    ['token_type' => $tokenType, 'expires_in' => $expires_in, 'ext_expires_in' => $ext_expires_in, 'access_token' => $access_token] = $content;

    $this->setNextExpiration(Carbon::now(), $expires_in, $access_token);

    return $access_token;
  }

  private function handleErrorCodes($code)
  {
    // 
  }

  /**
   * Checks the timestamp to check whether the access_token timout has expires.
   *
   * @return boolean
   */
  private function isAuthenticated()
  {
    return ((Cache::get($this->cache_name)) ? $this->fetchCachedAccessToken() : false);
  }

  /**
   * Fetches the cached access token, reauths if not available.
   *
   * @return void
   */
  private function fetchCachedAccessToken()
  {
    $pl = Cache::get($this->cache_name);
    if (!$pl) return $this->auth();
    return json_decode($pl)->access_token;
  }

  /**
   * Sets the token expiration to support the checking of if is authenticated without
   * sending a new http request to the server, minimizing the requests needed to aquire
   * an access token.
   *
   * @param Carbon $time
   * @param Int $expTime
   * @return void
   */
  private function setNextExpiration(Carbon $time, Int $expTime, $accessKey)
  {
    //
    $expires_in = $time->addSeconds(($expTime - MSGMailerTransportConfig::TIMEOUT_DEVIATION));

    Cache::put(
      $this->cache_name,
      json_encode(["expires_in" => $expires_in, "access_token" => $accessKey]),
      $expires_in
    );
  }
}
