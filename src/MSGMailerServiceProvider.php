<?php

namespace CH\MSGMailer;

use CH\MSGMailer;
use CH\MSGMailer\Transport\MSGMailerManager;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Support\ServiceProvider;

class MSGMailerServiceProvider extends MailServiceProvider
{
  public function boot()
  {
    $this->publishes(
      [
        __DIR__ . '/config/msgmailer.php' => config_path('msgmailer.php')
      ],
      'msgmailer'
    );

    // $this->publishes(
    //   [
    //     __DIR__ . '/Resources/views/*' => resources_path('')
    //   ],
    //   'msgmailer'
    // );
    if (env('MSGMAILER_USE_DB_CACHE')) {
      // $this->publishes(
      //   [
      //     __DIR__ . '/Resources/migrations/*' => database_path('/migrations/*')
      //   ],
      //   'msgmailer'
      // );
    }
  }

  public function register()
  {
    parent::register();
    $this->registerSwiftTransport();
  }

  protected function registerSwiftTransport()
  {
    $this->app->singleton('mail.manager', function ($app) {
      return new MSGMailerManager($app);
    });
  }
}
