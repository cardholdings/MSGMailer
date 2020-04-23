<?php

namespace CH\MSGMailer\Transport;

use Illuminate\Mail\Transport\Transport;
use Swift_Mime_SimpleMessage;
use Swift_Attachment;

use Illuminate\Support\Facades\Log;

class MSGMailerTransport extends Transport
{
  use MSGMailerTransportAuth;
  private $cache_name;

  public function __construct()
  {
    $this->cache_name = config('msgmailer.cache_name');
  }

  public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
  {

    $this->beforeSendPerformed($message);

    $token = $this->auth();


    $msgmailer = new \CH\MSGMailer\MSGMailClient(
      $token,
      [$this, 'auth'],
      $message
    );

    $response = $msgmailer->send();

    $this->sendPerformed($message, $response);

    return $this->numberOfRecipients($message);
  }

  /**
   * Get body for the message.
   *
   * @param \Swift_Mime_SimpleMessage $message
   * @return array
   */

  protected function getBody(Swift_Mime_SimpleMessage $message)
  {
    return [
      'From' => [
        'Email' => config('mail.from.address'),
        'Name' => config('mail.from.name')
      ],
      'To' => $this->getTo($message),
      'Subject' => $message->getSubject(),
      'HTMLPart' => $message->getBody(),
    ];
  }

  /**
   * Get the "to" payload field for the API request.
   *
   * @param \Swift_Mime_SimpleMessage $message
   * @return string
   */
  protected function getTo(Swift_Mime_SimpleMessage $message)
  {
    return collect($this->allContacts($message))->map(function ($display, $address) {
      return $display ? [
        'Email' => $address,
        'Name' => $display
      ] : [
        'Email' => $address,
      ];
    })->values()->toArray();
  }

  /**
   * Get all of the contacts for the message.
   *
   * @param \Swift_Mime_SimpleMessage $message
   * @return array
   */
  protected function allContacts(Swift_Mime_SimpleMessage $message)
  {
    return array_merge(
      (array) $message->getTo(),
      (array) $message->getCc(),
      (array) $message->getBcc()
    );
  }
}
