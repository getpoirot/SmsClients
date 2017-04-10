<?php

return [
    \Module\SmsClients\Module::CONF_KEY =>
    [
        \Module\SmsClients\Services\ServiceSmsClient::CONF_CLIENT => [
            // 'service' => '/Registered/ServiceName'
            // 'service' => iClientOfSms
               /*
               'service' => new \Poirot\Ioc\instance(
                   \Poirot\Sms\Driver\KavehNegar\Sms::class
                   , [
                       'api_key'  => '7136787A396169757A7A6D4D714B44343330336F67773D3D',
                       'platform' => new \Poirot\Ioc\instance(
                           \Poirot\Sms\Driver\KavehNegar\Rest\PlatformRest::class
                       )
                   ]
               ),
               */
        ],
    ],
];
