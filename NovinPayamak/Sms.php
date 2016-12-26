<?php
namespace Poirot\Sms\NovinPayamak;

use Poirot\ApiClient\aClient;
use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\ApiClient\Request\Command;
use Poirot\Sms\Exceptions\exMessageMalformed;
use Poirot\Sms\Exceptions\exMessaging;
use Poirot\Sms\Interfaces\iClientOfSMS;
use Poirot\Sms\Interfaces\iMessage;
use Poirot\Sms\Interfaces\iSentMessage;
use Poirot\Sms\NovinPayamak\Soap\PlatformSoap;
use Poirot\Sms\SMSentMessage;


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
     * @param iMessage $message Message
     *
     * @return iSentMessage Message with given uid
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
            'Recipients' => $recipients,
            'Message'    => array( $message->getBody() ),
            'Flash'      => $message->isFlash(),
            'DateTime'   => $message->getCreatedDate()->format('Y-M-D H:i:s'),
        ));

        # Send Through Platform
        $res = $this->call($command);
        if ($ex = $res->hasException())
            throw $ex;

        # Message With Given UID from Server
        $res = $res->expected();
        $rMessage = new SMSentMessage;
        $rMessage
            ->setUID($res->MessageId)
            ->setBody($message->getBody())
            ->setFlash($message->isFlash())
            ->setCoding($message->getCoding())
            ->setRecipients($recipients)
        ;

        return $rMessage;
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
        # Make Command
        $command = $this->_newCommand('MessageStatus', array(
            'MessageId' => $message->getUID(),
        ));

        # Send Through Platform
        $res = $this->call($command);
        if ($ex = $res->hasException())
            throw $ex;

        $res = $res->expected();

        $inf = json_decode($res->Info);

        $return = array();
        foreach($inf->Recipients as $r) {
            switch($r->status) {
                case 0:  $status = iMessage::STATUS_SENT;      break;
                case 1:  $status = iMessage::STATUS_DELIVERED; break;
                case -1: $status = iMessage::STATUS_PENDING;   break;
                case -2: $status = iMessage::STATUS_FAILED;    break;
                case -5: $status = iMessage::STATUS_PENDING;   break;
                default: $status = iMessage::STATUS_UNKNOWN;
            }

            $return[$r->cell] = $status;
        }

        return $return;
    }

    /**
     * Get Inbox
     *
     * @param int $offset
     * @param int $page
     *
     * @return mixed
     */
    function getInbox($offset = null, $page = null)
    {
        # Make Command
        $conditions = array('Type' => 'All');
        ($offset === null) ?: $conditions['LastMessageId‬‬'] = $offset;
        ($page   === null) ?: $conditions['Page‬‬'] = $page;
        $command = $this->_newCommand('Inbox', array(
            'Conditions' => json_encode($conditions)
        ));

        # Send Through Platform
        $res = $this->call($command);
        if ($ex = $res->hasException())
            throw $ex;

        $res = $res->expected();
        // TODO Generalization
        return $res->List;
    }

    /**
     * Get Remaining Account Credit
     *
     * @return int
     */
    function getRemainCredit()
    {
        # Make Command
        $command = $this->_newCommand('CheckCredit');

        # Send Through Platform
        $res = $this->call($command);
        if ($ex = $res->hasException())
            throw $ex;

        $res = $res->expected();
        return $res->Credit;
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
            // TODO Detect Best Platform Match
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
