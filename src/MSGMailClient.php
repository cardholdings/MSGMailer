<?php

namespace CH\MSGMailer;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\RequestException;

use Swift_Mime_SimpleMessage;
use Swift_Attachment;
use Swift_MimePart;

class MSGMailClient
{

  private $accessToken;
  private $fromEmail;
  private $fromName;
  private $msg;
  private $recipients;
  private $bccRecipients;
  private $ccRecipients;
  private $fileAttachments;
  private $mimePartAttachments;

  /**
   * Creates and transforms the Swift_Mime_SimpleMessage to a valid json representation needed
   * for the Microsoft Graph <SendMail> endpoint.
   *
   * @param string $access_token
   * @param closure $reauth
   * @param Swift_Mime_SimpleMessage $message
   */
  public function __construct(string $access_token, $reauth, Swift_Mime_SimpleMessage $message)
  {
    $this->accessToken = $access_token;
    $this->msg = $message;
    [$this->fromEmail, $this->fromName] = [array_keys($this->msg->getFrom())[0], array_values($this->msg->getFrom())[0]];
    // dd($this->msg->getTo());
    // dd($this->msg->getBcc());
    // dd(spitItOut($this->msg));
    // dd(Swift_Attachment::class);
    $this->fileAttachments = array_filter($this->msg->getChildren(), 'CH\MSGMailer\MapFilters::filterFileAttachments');
    $this->mimePartAttachments = array_filter($this->msg->getChildren(), 'CH\MSGMailer\MapFilters::filterMimePartAttachments');





    $this->fileAttachments = count($this->fileAttachments) ?
      $this->prepareFileAttachments($this->fileAttachments) :
      false;

    $this->mimePartAttachments = count($this->mimePartAttachments) ?
      $this->prepareMimePartAttachments($this->mimePartAttachments) :
      false;

    $this->recipients = $this->prepareRecipients($this->msg->getTo());

    $this->bccRecipients = $this->msg->getBcc() ?
      $this->prepareRecipients($this->msg->getBcc()) :
      false;

    $this->ccRecipients = $this->msg->getCc() ?
      $this->prepareRecipients($this->msg->getCc()) :
      false;

    // $this->jsonPayload = json_encode($this->generateJsonPayload());
  }

  private function prepareRecipients(array $rec)
  {
    // 
    $grp = [];

    foreach ($rec as $email => $name) {
      array_push($grp, ["emailAddress" => [
        "address" => $email,
        "name" => $name
      ]]);
    }

    return $grp;
  }
  /**
   * Takes in an array of Swift_Attachment(s), and transforms them into an array with associative array
   * to get ready to convert to a json format to ship with the rest request.
   *
   * @param array $attachments
   * @return array $grp
   */
  private function prepareFileAttachments(array $attachments)
  {
    // 
    $grp = [];
    foreach ($attachments as $att) {
      array_push($grp, [
        "@odata.type" => "#microsoft.graph.fileAttachment",
        "Name" => $att->getFilename(),
        "ContentType" => $att->getContentType(),
        "ContentBytes" => base64_encode($att->getBody()),
      ]);
    }

    return $grp;
  }

  private function prepareMimePartAttachments(array $attachments)
  {
    // 
  }


  public function send()
  {
    // We want to catch the sending errors and try to fix them, like if the bearer key has expired etc.
    try {

      $cl = new GuzzleClient();
      // dd(json_encode($this->generateJsonPayload()));
      $res = $cl->request(
        MSGMailClientConfig::METHOD,
        $this->getPostRequestLocation(),
        [
          'headers' => $this->getPostHeadersRequired(),
          'json' => $this->generateJsonPayload()
        ]
      );

      return $res;
    } catch (ClientException $th) {
      throw $th;
    }
  }

  private function getPostRequestLocation()
  {
    // 
    return MSGMailClientConfig::MAIN_BASE_URL . "/" . MSGMailClientConfig::API_VERSION . "/users/" . $this->fromEmail . "/sendMail";
  }

  private function getPostHeadersRequired()
  {
    return [
      "Authorization" => 'Bearer ' . $this->accessToken,
      "Content-Type" => MSGMailClientConfig::CONTENT_TYPE,
      "Accept" => MSGMailClientConfig::CONTENT_TYPE
    ];
  }

  private function getImportance()
  {
    $imp = ["Highest", "High", "Normal", "Low", "Lowest"];
    return $imp[$this->msg->getPriority()];
  }

  private function getMsContentType()
  {
    $map = ["text/plain" => "text", "text/html" => "html"];

    return $map[$this->msg->getBodyContentType()];
  }

  private function generateJsonPayload()
  {
    //
    $payload = [
      "message" => [
        "subject" => $this->msg->getSubject(),
        "importance" => $this->getImportance(),
        "toRecipients" => $this->recipients,
        // "bodyPreview" => "",
        "body" => [
          "contentType" => $this->getMsContentType(),
          "content" => $this->msg->getBody(),
        ],
        "internetMessageHeaders" => [
          [
            "name" => "X-Priority",
            "value" => strval($this->msg->getPriority())
          ]
        ],
      ],
      "saveToSentItems" => MSGMailClientConfig::SAVE_TO_SENT
    ];

    if ($this->fileAttachments) $payload["message"]["Attachments"] = $this->fileAttachments;
    if ($this->ccRecipients) $payload["message"]["ccRecipients"] = $this->ccRecipients;

    return $payload;
  }
}
