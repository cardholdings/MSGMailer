<?php
return [
  /**
   * 
   * Your Microsoft Graph application's Tenant ID
   */
  'tenant_id' => env('MSGMAILER_TENANT_ID'),
  /**
   * 
   * Your applications client ID;
   */
  'client_id' => env('MSGMAILER_CLIENT_ID'),
  /**
   * 
   * Your applications client secret;
   */
  'client_secret' => env('MSGMAILER_CLIENT_SECRET'),
  /**
   * 
   * The scope of which you would like the app to have access to.
   * By default it is set to /.default and the permissions are handlet
   * at the API endpoint control center!
   */
  'scope' => env('MSGMAILER_SCOPE', "https://graph.microsoft.com/.default"),
  /**
   * 
   * Grant Type, should be set to client credentials.
   */
  'grant_type' => env('MSGMAILER_GRANT_TYPE', "client_credentials"),
  /**
   * 
   * Cache name for persistant storage of access token and timout value
   * Minimizes the request calls we have to make to the authorization end at microsoft,
   * and actually utilizing the key for an whole hour before getting a new one!
   */
  'cache_name' => env('MSGMAILER_CACHE_NAME', "msgmailer.access"),
  /**
   * 
   * Save the sent message to sent items on the mail account.
   */
  'store_sent' => false,

];
