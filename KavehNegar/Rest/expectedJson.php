<?php
namespace Poirot\Sms\KavehNegar\Rest;

use Poirot\ApiClient\Interfaces\Response\iResponse;
use Poirot\ApiClient\Response\aExpectedResponse;


class expectedJson
    extends aExpectedResponse
{
    /**
     * Get Expected Result From Response Raw Body
     *
     * @param string $originResult Raw Response Body
     * @param iResponse $self
     *
     * @return mixed
     */
    function __invoke($originResult, iResponse $self)
    {
        $json_response = json_decode($originResult);
        $json_return = $json_response->return;
        if ($json_return->status != 200) {
            throw new ApiException($json_return->message, $json_return->status);
        }

        return $json_response->entries;
    }
}