<?php

namespace CH\MSGMailer;

class MSGMailClientConfig
{
  const WRAPPER_VERSION = 'v1.0';
  const USER_AGENT = 'msgmailer-api';
  const SECURED = true;
  const MAIN_BASE_URL = "https://graph.microsoft.com";
  const API_VERSION = "v1.0";
  const CONTENT_TYPE = 'application/json';
  const METHOD = 'POST';
  const SAVE_TO_SENT = false;
  const SPLIT_IF_MULTIPLE = false;
}
