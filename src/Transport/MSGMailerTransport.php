<?php

namespace CH\MSGMailer\Transport;

use Illuminate\Mail\Transport\Transport;
use Swift_Mime_SimpleMessage;

use Illuminate\Support\Facades\Log;

class MSGMailerTransport extends Transport
{

  public function __construct()
  {
    $this->transportAuthentication = new MSGMailerTransportAuth();
  }

  public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
  {

    $this->beforeSendPerformed($message);

    $token = $this->transportAuthentication->auth();
    Log::info($token);
    $msgmailer = new \CH\MSGMailer\Client(
      $token
    );

    $response = $msgmailer->post('send', ['body' => $this->getBody($message)]);

    $this->sendPerformed($message);

    return $this->numberOfRecipients($message);
  }

  private function getAccessToken()
  {
    return $this->accessToken;
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
      'Messages' => [
        [
          'From' => [
            'Email' => config('mail.from.address'),
            'Name' => config('mail.from.name')
          ],
          'To' => $this->getTo($message),
          'Subject' => $message->getSubject(),
          'HTMLPart' => $message->getBody(),
        ]
      ]
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
