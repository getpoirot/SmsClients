<?php
return [
    'provider' => new \Poirot\Ioc\instance(
        \Poirot\Sms\Driver\KavehNegar\Sms::class
        , [
            'api_key'  => 'xxx',
            'platform' => new \Poirot\Ioc\instance(
                \Poirot\Sms\Driver\KavehNegar\Rest\PlatformRest::class
            )
        ]
    ),
];