<?php
namespace Poirot\Sms\NovinPayamak\Soap;

use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\ApiClient\Interfaces\Request\iApiCommand;
use Poirot\ApiClient\Interfaces\Response\iResponse;
use Poirot\ApiClient\Request\Command;
use Poirot\Std\ConfigurableSetter;


class PlatformSoap
    extends ConfigurableSetter
    implements iPlatform
{
    /** @var Command */
    protected $Command;

    protected $serverUrl = 'http://www.novinpayamak.com/services/SMSBox/wsdl';

    /** @var \SoapClient */
    protected $conn;
    protected $soapOptions = array();


    /**
     * Build Platform Specific Expression To Send Trough Transporter
     *
     * @param iApiCommand $command Method Interface
     *
     * @return iPlatform Self or Copy/Clone
     */
    function withCommand(iApiCommand $command)
    {
        $self = clone $this;
        $self->Command = $command;
        return $self;
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
        if (!$command = $this->Command)
            throw new \Exception('No Command Is Specified.');


        $soap      = $this->_getConnect();

        $arguments = $command->getArguments();
        $r         = call_user_func(array($soap, $command->getMethod()), $arguments);

        $response  = new Response;
        $response->withRawBody($r);

        return $response;
    }


    // Options

    /**
     * Set Server Url
     * @param string $serverUrl
     * @return $this
     */
    function setServerUrl($serverUrl)
    {
        $this->conn = null;

        $this->serverUrl = (string) $serverUrl;
        return $this;
    }

    /**
     * Get Server Url
     * @return string
     */
    function getServerUrl()
    {
        return $this->serverUrl;
    }

    /**
     * Set Soap Server Options
     * @link http://php.net/manual/en/soapclient.soapclient.php
     *
     * @param array $options
     *
     * @return $this
     */
    function setSoapOptions(array $options)
    {
        $this->conn = null;

        $this->soapOptions = $options;
        return $this;
    }

    /**
     * Get Soap Client Options
     * @return array
     */
    function getSoapOptions()
    {
        return $this->soapOptions;
    }


    // ..

    /**
     * @return \SoapClient
     */
    protected function _getConnect()
    {
        if ($this->conn)
            return $this->conn;

        $wsdLink = $this->getServerUrl();
        if ($soapOptions = $this->getSoapOptions())
            $conn = new \SoapClient($wsdLink, $soapOptions);
        else
            $conn = new \SoapClient($wsdLink);

        return $this->conn  = $conn;
    }

    function __clone()
    {
        $this->conn = null;
    }
}
