<?php
namespace Poirot\Sms\NovinPayamak;

use Poirot\ApiClient\aClient;
use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\ApiClient\Request\Command;
use Poirot\Sms\Exceptions\exMessaging;
use Poirot\Sms\iClientOfSMS;
use Poirot\Sms\iSMessage;
use Poirot\Sms\Providers\NovinPayamak\PlatformSoap;


class Sms
    extends aClient
    implements iClientOfSMS
{
    protected $authNumber;
    protected $authPass;

    /**
     * Send Message To Recipients
     *
     * @param array $recipients Receivers of message
     * @param iSMessage $message Message
     *
     * @return iSMessage Message with given uid
     * @throws exMessaging
     */
    function sendTo(array $recipients, iSMessage $message)
    {
        // TODO check empty message

        $command = $this->_newCommand('Send',array(
            'Recipients' => $recipients,
            'Message'    => $message->getBody(),
            'Flash'      => $message->isFlash(),
            'DateTime'   => $message->getCreatedDate(),
        ));

        return $this->call($command);
    }

    /**
     * Get Message Delivery Status
     *
     * @param string $messageUid
     *
     * @return string
     */
    function getMessageStatus($messageUid)
    {
        // TODO: Implement getMessageStatus() method.
    }

    /**
     * List All Sent Messages
     *
     * !! usually providers list messages for current date only
     *
     * @param int $limit
     *
     * @return  []iSMessage
     */
    function listSentMessages($limit)
    {
        // TODO: Implement listSentMessages() method.
    }

    /**
     * Get Remaining Account Credit
     *
     * @return int
     */
    function getRemainCredit()
    {
        // TODO: Implement getRemainCredit() method.
    }

    /**
     * Get Messaging Line Number
     *
     * @return string
     */
    function getLineNumber()
    {
        // TODO: Implement getLineNumber() method.
    }


    // Implement iClient

    /**
     * Get Client Platform
     *
     * - used by request to build params for
     *   server execution call and response
     *
     * @return iPlatform
     */
    function platform()
    {
        if (!$this->platform)
            $this->setPlatform(new PlatformSoap);

        return $this->platform;
    }


    // Options

    /**
     * Set Platform
     *
     * @param iPlatform $platform
     *
     * @return $this
     */
    function setPlatform(iPlatform $platform)
    {
        $this->platform = $platform;
        return $this;
    }

    /**
     * Set Authorization Number
     * ! used by Auth() command
     *
     * @param string $number
     *
     * @return $this
     */
    function setAuthNumber($number)
    {
        $this->authNumber = (string) $number;
        return $this;
    }

    /**
     * Set Auth Password
     * ! used by Auth() command
     *
     * @param string $pass
     *
     * @return $this
     */
    function setAuthPass($pass)
    {
        $this->authPass = (string) $pass;
        return $this;
    }

    // ..

    protected function _newCommand($methodName, array $args = null)
    {
        $method = new Command;
        $method->setMethod($methodName);
        ## account data options
        ## these arguments is mandatory on each call
        $defAccParams = array(
            'Auth'    => array(
                'number' => $this->authNumber,
                'pass'   => $this->authPass,
            ),
        );
        $args = ($args !== null) ? array_merge($defAccParams, $args) : $defAccParams;
        $method->setArguments($args);
        return $method;
    }
}
