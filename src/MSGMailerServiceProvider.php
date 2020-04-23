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
    //     __DIR__ . '/Resources/views' => resources_path('views/vendor/')
    //   ],
    //   'msgmailer:views'
    // );

    // $this->loadViews(__DIR__.'/Resources/views', 'msgmailer');
    if (env('MSGMAILER_USE_CACHE')) {
      // $this->publishes(
      //   [
      //     __DIR__ . '/Database/migrations' => database_path('/migrations')
      //   ],
      //   'msgmailer:migration'
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
