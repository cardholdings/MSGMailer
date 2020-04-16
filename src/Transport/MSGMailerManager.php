<?php

namespace CH\MSGMailer\Transport;

use Illuminate\Mail\MailManager;

class MSGMailerManager extends MailManager
{
  protected function createMSGMailerTransport()
  {
    return new MSGMailerTransport();
  }
}
