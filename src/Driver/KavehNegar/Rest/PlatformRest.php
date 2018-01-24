<?php
namespace Poirot\Sms\Driver\KavehNegar\Rest;

use Poirot\ApiClient\Exceptions\exConnection;
use Poirot\ApiClient\Exceptions\exHttpResponse;
use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\ApiClient\Interfaces\Request\iApiCommand;
use Poirot\ApiClient\Interfaces\Response\iResponse;
use Poirot\ApiClient\Request\Command;
use Poirot\Std\ConfigurableSetter;


class PlatformRest
    extends ConfigurableSetter
    implements iPlatform
{
    /** @var Command */
    protected $Command;

    // Options:
    protected $usingSsl  = false;
    protected $serverUrl = 'http://api.kavenegar.com/v1';


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


        # Get Connect To Server and Send Data

        $cmArguments = $command->getArguments();

        $data = [];
        foreach ($cmArguments as $key => $val) {
            // Filter null values ...
            if ($val === null) continue;

            $data[$key] = $val;
        }

        $response = $this->_sendPostViaCurl($this->_getServerHttpFullUrlFromCommand($command), $data);
        return $response;
    }


    // Options

    /**
     * Set Server Url
     *
     * @param string $url
     *
     * @return $this
     */
    function setServerUrl($url)
    {
        $this->serverUrl = (string) $url;
        return $this;
    }

    /**
     * Server Url
     *
     * @return string
     */
    function getServerUrl()
    {
        return $this->serverUrl;
    }

    /**
     * Using SSl While Send Request To Server
     *
     * @param bool $flag
     *
     * @return $this
     */
    function setUsingSsl($flag = true)
    {
        $this->usingSsl = (bool) $flag;
        return $this;
    }

    /**
     * Ssl Enabled?
     *
     * @return bool
     */
    function isUsingSsl()
    {
        return $this->usingSsl;
    }


    // ..

    protected function _sendPostViaCurl($url, $data)
    {
        if (!extension_loaded('curl'))
            throw new \Exception('cURL library is not loaded');

        $headers       = array(
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'charset: utf-8'
        );


        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_POST, true);

        # build request body
        $urlEncodeData = http_build_query($data);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $urlEncodeData);

        # Send Post Request
        $cResponse     = curl_exec($handle);
        $cResponseCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $cContentType  = curl_getinfo($handle, CURLINFO_CONTENT_TYPE);


        if ($curl_errno = curl_errno($handle)) {
            // Connection Error
            $curl_error = curl_error($handle);
            $errorMessage = $curl_error.' '."When GET: $url";
            throw new exConnection($errorMessage, $curl_errno);
        }

        $exception = null;
        if (! ($cResponseCode >= 200 && $cResponseCode < 300) ) {
            $message = $cResponse;
            if ($cResponseCode >= 300 && $cResponseCode < 400)
                $message = 'Response Redirected To Another Uri.';

            $exception = new exHttpResponse($message, $cResponseCode);
        }

        $response = new Response(
            $cResponse
            , $cResponseCode
            , ['content_type' => $cContentType]
            , $exception
        );

        return $response;
    }

    /**
     * Determine Server Http Url Using Http or Https?
     *
     * provide:
     * http://api.kavenegar.com/v1/7136787A396169757A7A6D4D714B44343330336F67773D3D/sms/send.json
     *
     * @param Command $command
     *
     * @return string
     * @throws \Exception
     */
    protected function _getServerHttpFullUrlFromCommand(Command $command, $base = 'sms')
    {
        $cmMethod = strtolower($command->getMethodName());

        switch ($cmMethod) {
            case 'info':
                $base = 'account';
                break;
            case 'lookup':
                $base = 'verify';
                break;
        }

        $apiKey   = $command->getArguments();
        $apiKey   = (isset($apiKey['apiKey'])) ? $apiKey['apiKey'] : null;
        if (!$apiKey)
            throw new \Exception('Command must include Api Key.');


        $serverUrl = rtrim($this->getServerUrl(), '/');
        $serverUrl.='/'.$apiKey.'/'.$base.'/'.$cmMethod.'.json';

        return $serverUrl;
    }
}
