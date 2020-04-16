<?php

namespace CH\MSGMailer\Transport;

class MSGMailerTransportConfig
{
  const SECURED = true;
  const CONN_TYPE = 'https://';
  const TOKEN_REQ_URL = 'login.microsoftonline.com';
  const TOKEN_REQ_ENDPOINT = 'oauth2/v2.0/token';
  const CONTENT_TYPE = 'application/x-www-form-urlencoded';
  const METHOD = 'POST';
  const TIMEOUT_DEVIATION = 2;
}
