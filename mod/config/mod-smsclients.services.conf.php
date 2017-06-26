<?php
/**
 * @see \Poirot\Ioc\Container\BuildContainer
 *
 * ! These Services Can Be Override By Name (also from other modules).
 *   Nested in IOC here at: /module/smsclients/services
 *
 *
 * @see \Module\SmsClients::getServices()
 */
use Poirot\Ioc\instance;

return [
    'implementations' => [
        'sms' => \Poirot\Sms\Interfaces\iClientOfSMS::class,
    ],
    'services' => [
        'sms' => new instance(
            \Module\SmsClients\Services\ServiceSmsClient::class
            , \Poirot\Std\catchIt(function () {
                if (false === $c = \Poirot\Config\load(__DIR__.'/smsclient/client-service'))
                    throw new \Exception('Config (smsclient/client-service) not loaded.');

                return $c->value;
            })
        ),
    ],
];
