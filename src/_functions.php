<?php
namespace Poirot\Sms
{
    /**
     * Is Valid Mobile Number?
     *
     * @param string $mobileNumber
     *
     * @return boolean
     */
    function isValidMobileNum($mobileNumber)
    {
        $pattern = '/^[- .\(\)]?((98)|(\+98)|(0098)|0){1}[- .\(\)]{0,3}((90)|(91)|(92)|(93)|(99)){1}[0-9]{8}$/';
        return preg_match($pattern, (string) $mobileNumber);
    }
}
