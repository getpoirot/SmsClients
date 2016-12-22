<?php
namespace Poirot\Sms\NovinPayamak\Rest;

use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\ApiClient\Interfaces\Request\iApiCommand;
use Poirot\ApiClient\Interfaces\Response\iResponse;
use Poirot\ApiClient\Request\Command;


class PlatformRest
    implements iPlatform
{
    /** @var Command */
    protected $Command;

    protected $serverUrl = 'http://novinpayamak.com/rest_sms_boxs';

    protected $conn;


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


        // TODO

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


    // ..

    /**
     * @return \SoapClient
     */
    protected function _getConnect()
    {
        if ($this->conn)
            return $this->conn;

        // TODO
    }

    function __clone()
    {
        $this->conn = null;
    }
}
