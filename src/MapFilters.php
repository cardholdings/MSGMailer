<?php

namespace CH\MSGMailer;

use Swift_Attachment;
use Swift_MimePart;

class MapFilters
{
  public static function filterFileAttachments($n){
    return($n instanceof Swift_Attachment);
  }
  public static function filterMimePartAttachments($n){
    return($n instanceof Swift_MimePart);
  }
}
