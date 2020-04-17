# MSGMailer for Laravel v7+

Add Microsoft Graph Send.Mail capability to your Laravel application, sends the mails over the Microsoft Graph API

## Install

Include with composer.
Run:

```
php artisan vendor:publish --tag=msgmailer
```

Comment out:

```php
Illuminate\Mail\MailServiceProvider::class,
```

in your `./app/config/app.php` file.

```.env
MSGMAILER_TENANT_ID={YOUR MSGRAPH TENANT ID}
MSGMAILER_CLIENT_ID={YOUR MSGRAPH CLIENT ID}
MSGMAILER_CLIENT_SECRET={YOUR MSGRAPH CLIENT SECRET}
MSGMAILER_TABLE_NAME={YOUR DB TABLE NAME FOR PERSISTENT ACCESS_KEY}
```

In your `./app/config/mail.php` add 'msgmailer' under `mailers`

```php
'msgmailer' => [
            'transport' => 'msgmailer',
        ]
```

## Test

```php
php artisan tinker
>>> Mail::send('root', [],
        function ($message) {
          $message->
          to('yourmail@yourdomain.ex')->
          subject('this works!');
        }
    );
```
