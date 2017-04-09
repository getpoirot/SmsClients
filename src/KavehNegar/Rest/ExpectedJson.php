<?php
namespace Poirot\Sms\KavehNegar\Rest;

use Poirot\ApiClient\Interfaces\Response\iResponse;
use Poirot\ApiClient\Response\aExpectedResponse;
use Poirot\Std\Struct\DataEntity;


class ExpectedJson
    extends aExpectedResponse
{
    /**
     * Get Expected Result From Response Raw Body
     *
     * @param string    $originResult Raw Response Body
     * @param iResponse $self
     *
     * @return mixed
     * @throws \Exception
     */
    function __invoke($originResult, iResponse $self)
    {
        if (false === $result = json_decode($originResult, true))
            throw new \Exception('Server Response Cant Parse To Json.');


        return new DataEntity($result);
    }
}