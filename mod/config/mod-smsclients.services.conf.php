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
return [
    'implementations' => [
        'sms' => \Poirot\Sms\Interfaces\iClientOfSMS::class,
    ],
    'services' => [
        'sms' => \Module\SmsClients\Services\ServiceSmsClient::class,
    ],
];
