<?php
namespace Poirot\Sms\KavehNegar\Rest;

use Poirot\ApiClient\Exceptions\exHttpResponse;
use Poirot\ApiClient\ResponseOfClient;
use Poirot\Sms\Exceptions\exAuthNoCredit;
use Poirot\Sms\Exceptions\exMessageMalformed;
use Poirot\Sms\Exceptions\exUnknownError;
use Poirot\Std\Struct\DataEntity;


class Response
    extends ResponseOfClient
{
    /**
     * Has Exception?
     *
     * @return \Exception|false
     */
    function hasException()
    {
        $exception     = $this->exception;
        $exceptionCode = null;

        if ($exception instanceof exHttpResponse)
        {
            // While Call To Server From Platform Http Response Instead Of 200 Returned.
            // Get Code
            $exceptionCode = $exception->getCode();
        }
        else
        {
            // Check exception from raw response
            // Server Response Status is 200 but Logical Error May Happen And Returned in Response Body

            $expected = $this->expected();
            if ($expected instanceof DataEntity) {
                // Response Body Can parsed To Data Structure
                $expectedReturn = $expected->get('return');
                if ($expectedReturn['status'] != 200)
                    $exceptionCode = $expectedReturn['status'];
            }
        }

        if ($exceptionCode === null)
            return false;


        switch ($exceptionCode) {
            case 414: $return = new exMessageMalformed('To Many Receptor To Sent.');
                break;
            case 417: $return = new exMessageMalformed('Data Parameter Is Expired Or Malformed.');
                break;
            case 418: $return = new exAuthNoCredit('Credit Exudes.');
                break;

            default: $return = new exUnknownError;
        }

        return $return;
    }

    /**
     * Process Raw Body As Result
     *
     * :proc
     * mixed function($originResult, $self);
     *
     * @param callable $callable
     *
     * @return mixed
     */
    function expected(/*callable*/ $callable = null)
    {
        if ( $callable === null && false !== strpos($this->getMeta('content_type'), 'application/json') )
            // Retrieve Json Parsed Data Result
            $callable = new ExpectedJson;

        return parent::expected($callable);
    }
}
