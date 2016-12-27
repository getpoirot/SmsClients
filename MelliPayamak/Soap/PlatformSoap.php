<?php
namespace Poirot\Sms\MelliPayamak\Soap;

use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\ApiClient\Interfaces\Request\iApiCommand;
use Poirot\ApiClient\Interfaces\Response\iResponse;
use Poirot\ApiClient\Request\Command;
use Poirot\ApiClient\ResponseOfClient;
use Poirot\Sms\Exceptions\exServerError;
use Poirot\Std\ConfigurableSetter;


class PlatformSoap
    extends ConfigurableSetter
    implements iPlatform
{
    /** @var Command */
    protected $Command;

    protected $serverUrlSend = 'http://api.payamak-panel.com/post/send.asmx?wsdl';
    protected $serverUrlRecv = 'http://api.payamak-panel.com/post/receive.asmx?wsdl';

    /** @var []\SoapClient */
    protected $conn = array();
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


        $soap = $this->_getConnect(
            $this->_getServerUrlFromCommand($command)
        );

        try {
            $r = call_user_func(array($soap, $command->getMethod()), $command->getArguments());
        } catch (\Exception $e) {
            return $this->_newResponse()->setException(
                new exServerError('Server was unable to process request.', null, $e)
            );
        }

        return $this->_newResponse()->setRawResponse($r);
    }


    // Options

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

    /** @return \SoapClient */
    protected function _getConnect($serverUrl)
    {
        if (isset($this->conn[$serverUrl]))
            return $this->conn[$serverUrl];

        $wsdLink = $serverUrl;
        if ($soapOptions = $this->getSoapOptions())
            $conn = new \SoapClient($wsdLink, $soapOptions);
        else
            $conn = new \SoapClient($wsdLink);

        return $this->conn[$serverUrl] = $conn;
    }

    protected function _getServerUrlFromCommand(Command $command)
    {
        switch($command->getMethod()) {
//            case 'GetMessages':
            case 'GetInboxCount':
                return $this->serverUrlRecv;
                break;
        }

        return $this->serverUrlSend;
    }

    /** @return ResponseOfClient */
    protected function _newResponse()
    {
        $response = new ResponseOfClient;
        $response->setDefaultExpected(function ($originResult, $self) {
            return $originResult;
        });

        return $response;
    }

    function __clone()
    {
        $this->conn = array();
    }
}
