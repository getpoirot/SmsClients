<?php

return [
    \Module\SmsClients\Module::CONF_KEY =>
    [
        \Module\SmsClients\Services\ServiceSmsClient::CONF_CLIENT => [
            // 'service' => '/Registered/ServiceName'
            // 'service' => iClientOfSms

               'service' => new \Poirot\Ioc\instance(
                   \Poirot\Sms\Driver\KavehNegar\Sms::class
                   , [
                       'api_key'  => '',
                       'platform' => new \Poirot\Ioc\instance(
                           \Poirot\Sms\Driver\KavehNegar\Rest\PlatformRest::class
                       )
                   ]
               ),
        ],
    ],
];
