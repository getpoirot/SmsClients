<?php
namespace Poirot\Sms\Providers\NovinPayamak;

use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\ApiClient\Interfaces\Request\iApiCommand;
use Poirot\ApiClient\Interfaces\Response\iResponse;
use Poirot\ApiClient\Request\Command;
use Poirot\Std\ConfigurableSetter;


class PlatformSoap
    extends ConfigurableSetter
    implements iPlatform
{
    protected $serverUrl = '‫‪http://www.novinpayamak.com/services/SMSBox/wsdl‬‬';
    protected $Command;


    /**
     * Build Platform Specific Expression To Send Trough Transporter
     *
     * @param iApiCommand $command Method Interface
     *
     * @return iPlatform Self or Copy/Clone
     */
    function withCommand(iApiCommand $command)
    {
        // TODO: Implement withCommand() method.
    }

    /**
     * Build Response with send Expression over Transporter
     *
     * - Result must be compatible with platform
     * - Throw exceptions if response has error
     *
     * @throws \Exception Command Not Set
     * @return iResponse
     */
    function send()
    {
        // TODO: Implement send() method.
    }


    // Options

    /**
     * Get Server Url
     * @return string
     */
    function getServerUrl()
    {
        return $this->serverUrl;
    }

    /**
     * Set Server Url
     * @param string $serverUrl
     * @return $this
     */
    function setServerUrl($serverUrl)
    {
        $this->serverUrl = (string) $serverUrl;
        return $this;
    }
}
