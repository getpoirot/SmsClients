<?php
namespace Poirot\Sms\KavehNegar;

use Poirot\ApiClient\aClient;
use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\ApiClient\Request\Command;
use Poirot\Sms\Exceptions\exMessageMalformed;
use Poirot\Sms\Exceptions\exMessaging;
use Poirot\Sms\Interfaces\iClientOfSMS;
use Poirot\Sms\Interfaces\iMessage;
use Poirot\Sms\Interfaces\iSentMessage;
use Poirot\Sms\KavehNegar\Rest\PlatformRest;
use Poirot\Sms\SMSentMessage;
use Poirot\Std\Struct\DataEntity;


class Sms
    extends aClient
    implements iClientOfSMS
{
    protected $apiKey;
    protected $sender;


    /**
     * Send Message To Recipients
     *
     * @param array $recipients Receivers of message
     * @param iMessage $message Message
     *
     * @return iSentMessage[] Message(s) with given uid
     * @throws exMessaging
     */
    function sendTo(array $recipients, iMessage $message)
    {
        $body = $message->getBody();
        if ( empty($body) && !($body === 0 || $body === '0') )
            throw new exMessageMalformed('Message is Empty.');

        # Validate Given Recipients Mobile Number
        foreach ($recipients as $rMobNum) {
            if (! \Poirot\Sms\isValidMobileNum($rMobNum))
                throw new exMessageMalformed(sprintf(
                    'Invalid Recipient Phone Number for (%s).'
                    , $rMobNum
                ));
        }


        # Make Command
        $command = $this->_newCommand('Send', array(
            'receptor' => implode(', ', $recipients),
            'message'  => $message->getBody(),
            'localid'  => $message->getUID(),
            'type'     => ($message->isFlash()) ? 0 : null,
            'date'     => $message->getDateTimeCreated()->getTimestamp(),
        ));

        # Send Through Platform
        $apiResponse = $this->call($command);
        if ($ex = $apiResponse->hasException())
            throw $ex;


        # Message With Given UID from Server
        /** @var DataEntity $apiResponse */
        $apiResponse = $apiResponse->expected();

        $result = [];
        foreach ($apiResponse->get('entries') as $entry) {
            $rMessage = new SMSentMessage;
            $rMessage
                ->setUID($entry['messageid'])
                ->setBody($entry['message'])
                ->setContributor($entry['sender'])
                ->setRecipients([$entry['receptor']])
                ->setStatus($this->_transMessageStatus($entry['status']))
                ->setFlash($message->isFlash())
                ->setCoding($message->getCoding())
            ;

            $result[$entry['receptor']] = $rMessage;
        }

        return $result;
    }

    /**
     * Get Message Delivery Status
     *
     * @param iSentMessage $message
     *
     * @return array[$recipient_number => iSMessage::STATUS_*]
     */
    function getMessageStatus(iSentMessage $message)
    {
        // TODO: Implement getMessageStatus() method.
    }

    /**
     * Get Inbox
     *
     * @param int $offset
     * @param int $count
     *
     * @return mixed
     */
    function getInbox($offset = null, $count = null)
    {
        // TODO: Implement getInbox() method.
    }

    /**
     * Count Total Message Inbox
     *
     * @return int
     */
    function getCountTotalInbox()
    {
        // TODO: Implement getCountTotalInbox() method.
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


    // ...

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
            $this->setPlatform(new PlatformRest);

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
     * @param string $apiKey
     *
     * @return $this
     */
    function setApiKey($apiKey)
    {
        $this->apiKey = (string) $apiKey;
        return $this;
    }

    /**
     * Set Sender Line Number
     *
     * note: if not given default will used
     *
     * @param string $lineNumber
     *
     * @return $this
     */
    function setSenderLine($lineNumber)
    {
        $this->sender = (string) $lineNumber;
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
            'apiKey' => $this->apiKey,
            'sender' => $this->sender,
        );
        $args = ($args !== null) ? array_merge($defAccParams, $args) : $defAccParams;
        $method->setArguments($args);
        return $method;
    }

    /**
     * Translate Message Status From Given Response To Global Meaningful Const
     *
     * @param int $status
     *
     * @return null|string
     */
    protected function _transMessageStatus($status)
    {
        $return = null;

        switch ($status) {
            case 1:
            case 2:
            case 11:
                $return = iSentMessage::STATUS_PENDING; break;
            case 4:
            case 5:
                $return = iSentMessage::STATUS_SENT; break;
            case 6:
                $return = iSentMessage::STATUS_FAILED; break;
            case 10:
                $return = iSentMessage::STATUS_DELIVERED; break;
            case 13:
                $return = iSentMessage::STATUS_NOTSENT; break;
            case 14:
                $return = iSentMessage::STATUS_BANNED; break;
            case 100:
                $return = iSentMessage::STATUS_FAILED; break;
        }

        return $return;
    }
}
