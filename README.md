# Sms API Clients And Poirot Sms Module

SMS Abstraction; Implementing Iranian SMS Service Providers.

- One Abstraction Interface, Many Providers
- Entity Model For SMS Message Has To Be Sent And Sent Message
- Sent Message Is Serializable Include Receptors and Contributor Number
- Good Object Oriented Design Achieve Ability To Use Multiple Platform (rest, soap, ..) For Each Provider
- Simple Usage And Easy To Understand
- Can Be Used in Poirot Module Ecosystem 

# Poirot Module

## Configuration Settings

```php
return [
    \Module\SmsClients\Module::CONF_KEY =>
    [
        \Module\SmsClients\Services\ServiceSmsClient::CONF_CLIENT => [
            // 'service' => '/Registered/ServiceName'
            // 'service' => iClientOfSms
               // instance() let ioc to inject dependencies if required
               'service' => new \Poirot\Ioc\instance(
                   \Poirot\Sms\Driver\KavehNegar\Sms::class
                   , [
                       'api_key'  => 'your_api_key',
                       'platform' => new \Poirot\Ioc\instance(
                           \Poirot\Sms\Driver\KavehNegar\Rest\PlatformRest::class
                       )
                   ]
               ),
        ],
    ],
];
```

## Usage

```php
// Every Where In Project

$sentSms = \Module\SmsClients\Services\IOC::Sms()->sendTo(...)

```

## Providers

### Kaveh Negar 

http://kavenegar.com/

```php
$sms = new Poirot\Sms\Driver\KavehNegar\Sms([
    'api_key'     => 'your_api_key',
    'sender_line' => '10007770070777'
]);

try {
    $sentMessage = $sms->sendTo(['0935xxxxxxx'], new SMSMessage('Hello ...'));
} catch (exAuthNoCredit $e) {
    // There is no left credit
    echo '﷼'.$sms->getRemainCredit();
    die();
}

// unique message id 
$messageId = $sentMessage->getUid();

if ($sentMessage->getStatus() === $sentMessage::STATUS_BANNED)
    die('User Has Banned SMS Messaging!!');

if ($sentMessage->getStatus() === $sentMessage::STATUS_FAILED)
    die('SMS Sent Successfuly But Failed Sending From Telecommunication!!');


// Sent Message is Serializable And Can Be Stored
$myPersitence->store($sentMessage);

// Later We Can Check The Status Of Message
$sentMessage = $sms->getSentMessageWithId($messageId);

```

## Exceptions
- exAuthentication
- exAuthNoCredit
- exMessageMalformed
- exServerBan
- exServerError

