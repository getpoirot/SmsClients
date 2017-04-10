<?php
namespace Module\SmsClients\Services;

use Poirot\Application\aSapi;
use Poirot\Ioc\Container\Service\aServiceContainer;
use Poirot\Sms\Interfaces\iClientOfSMS;
use Poirot\Std\Struct\DataEntity;


class ServiceSmsClient
    extends aServiceContainer
{
    const CONF_CLIENT = 'ServiceSmsClient';


    /**
     * Create Service
     *
     * note: check implementation is done by ioc; so don't
     *       check instanceof or implementation here further more
     *
     * @return iClientOfSMS
     * @throws \Exception
     */
    function newService()
    {
        $conf    = $this->_attainConf();
        if (!isset($conf['service']))
            throw new \Exception('"service" as a key not found in config.');

        $service = $conf['service'];
        if (is_string($service)) {
            // Looking For Registered Service
            if (!$this->services()->has($service))
                throw new \Exception(sprintf(
                    'Try to retrieve SmsClient Service From (%s) but not found.'
                    , $service
                ));

            return $this->services()->get($service);
        }


        return $service;
    }


    // ..

    /**
     * Attain Merged Module Configuration
     * @return array
     */
    protected function _attainConf()
    {
        $sc     = $this->services();
        /** @var aSapi $sapi */
        $sapi   = $sc->get('/sapi');
        /** @var DataEntity $config */
        $config = $sapi->config();
        $config = $config->get(\Module\SmsClients\Module::CONF_KEY);

        $r = array();
        if (is_array($config) && isset($config[static::CONF_CLIENT]))
            $r = $config[static::CONF_CLIENT];

        return $r;
    }

}
