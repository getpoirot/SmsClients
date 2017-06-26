<?php
namespace Module\SmsClients\Services;

use Poirot\Ioc\Container\Service\aServiceContainer;
use Poirot\Sms\Interfaces\iClientOfSMS;


/**
 * Sms Service:
 *
 *   \Module\SmsClients\Services\IOC::Sms()->sendTo(...)
 *
 *
 * Replace Sms Service Can be achieved from Configuration file
 * by registering new instance or service name.
 *
 */
class ServiceSmsClient
    extends aServiceContainer
{
    /**
     * Indicate to allow overriding service
     * with another service
     *
     * @var boolean
     */
    protected $allowOverride = false;

    protected $provider;


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
        $provider = $this->provider;
        if (is_string($provider)) {
            // Looking For Registered Service
            if (!$this->services()->has($provider))
                throw new \Exception(sprintf(
                    'Try to retrieve SmsClient Service From (%s) but not found.'
                    , $provider
                ));

            $provider = $this->services()->get($provider);
        }

        return $provider;
    }


    // ..

    /**
     * @param mixed $provider
     */
    function setProvider($provider)
    {
        $this->provider = $provider;
    }

}
