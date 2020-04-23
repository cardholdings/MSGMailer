<?php

namespace Tests;

use Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;


class MSGMailerTest extends TestCase
{
  /**
   * A basic feature test example.
   *
   * @return void
   */
  public function testExample()
  {

    $email = Mail::send('root', [], function ($message) {
      $message->from('john@johndoe.com', 'John Doe');
      $message->sender('john@johndoe.com', 'John Doe');
      $message->to('john@johndoe.com', 'John Doe');
      $message->cc('john@johndoe.com', 'John Doe');
      $message->bcc('john@johndoe.com', 'John Doe');
      $message->replyTo('john@johndoe.com', 'John Doe');
      $message->subject('Subject');
      $message->priority(3);
      $message->attach('pathToFile');
    });

    // $response->assertStatus(200);
  }
}
