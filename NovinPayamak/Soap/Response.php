<?php
namespace Poirot\Sms\NovinPayamak\Soap;

use Poirot\ApiClient\ResponseOfClient;
use Poirot\Sms\Exceptions\exAuthentication;
use Poirot\Sms\Exceptions\exAuthNoCredit;
use Poirot\Sms\Exceptions\exMessageMalformed;
use Poirot\Sms\Exceptions\exServerError;


class Response
    extends ResponseOfClient
{
    /**
     * Response constructor.
     * @param array|null|\Traversable $options
     */
    function __construct($options = null)
    {
        $this->defaultExpected = function ($originResult, $self) {
            return $originResult;
        };

        parent::__construct($options);
    }

    /**
     * Has Exception?
     *
     * @return \Exception|false
     */
    function hasException()
    {
        $return = false;

        if ($this->exception)
            return $this->exception;

        # check exception from raw response
        if (!$rawRes = $this->rawResponse)
            return $return;

        switch ($rawRes->Status) {
            case -11: $return = new exMessageMalformed;
                break;
            case -22: $return = new exAuthentication('Authentication Failed.');
                break;
            case -33: $return = new exAuthNoCredit('Credit Exudes.');
                break;
            case -44: $return = new exAuthentication('No Main Credit.');
                break;
            case -55: $return = new exServerError('The Request Data Is Not Accessible.');
                break;
            case -99: $return = new exServerError('Too Many Request Is Sent.');
                break;
        }

        return $return;
    }
}
