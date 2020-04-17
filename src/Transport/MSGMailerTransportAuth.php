<?php

namespace CH\MSGMailer\Transport;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

/**
 * Authentication class to minimieze the calls for a new request token...
 * 
 */
class MSGMailerTransportAuth
{
  private $tenantId;
  private $clientId;
  private $clientSecret;
  private $scope;
  private $grantType;

  public function __construct()
  {
    // Check if we need to auth again
    if (!$this->isAuthenticated()) config(["msgmailer.nextAuth" => Carbon::now()]);
    $this->tenantId = config('msgmailer.tenant_id');
    $this->clientId = config('msgmailer.client_id');
    $this->clientSecret = config('msgmailer.client_secret');
    $this->scope = config('msgmailer.scope');
    $this->grantType = config('msgmailer.grant_type');
  }
  /**
   * Puts the Request URI together and returns it.
   *
   * @return string
   */
  private function getTokenRequestUri()
  {
    return MSGMailerTransportConfig::CONN_TYPE . MSGMailerTransportConfig::TOKEN_REQ_URL .
      "/" . $this->tenantId .
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
      'client_id' => $this->clientId,
      'scope' => $this->scope,
      'client_secret' => $this->clientSecret,
      'grant_type' => $this->grantType
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
    if ($this->isAuthenticated()) return config("msgmailer.lastAccessToken");

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

    if (!$status_code == 200) throw new ServerException('Status Code not 200!');

    Log::info('statuscode: ' . $status_code);

    $content = json_decode($res->getBody(), true);
    dd($content);
    ['token_type' => $tokenType, 'expires_in' => $expires_in, 'ext_expires_in' => $ext_expires_in, 'access_token' => $access_token] = $content;

    Log::info('Token Type:  ' . $tokenType . "\n" . 'Expires In: ' . $expires_in . "\n" . 'Ext Expires In: ' . $ext_expires_in . "\n" . "Access Token: " . $access_token . "\n");

    $this->setNextExpiration(Carbon::now(), $expires_in);

    config(["msgmailer.lastAccessToken" => $access_token]);

    return $access_token;
  }

  /**
   * Checks the timestamp to check whether the access_token timout has expires.
   *
   * @return boolean
   */
  private function isAuthenticated()
  {
    //
    $now = Carbon::now();
    if ($now->gt(config("msgmailer.nextAuth"))) return false;
    Log::info("I am authenticated!, reuse the last token!");
    return true;
  }

  /**
   * Forces ReAuthentification and gets a new Access Token
   *
   * @return void
   */
  private function forceReAuth()
  {
    // 
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
  private function setNextExpiration(Carbon $time, Int $expTime)
  {
    //
    config(["msgmailer.nextAuth" => $time->addSeconds(($expTime - MSGMailerTransportConfig::TIMEOUT_DEVIATION))]);
  }
}
