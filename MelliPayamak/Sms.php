<?php
namespace Poirot\Sms\MelliPayamak;

use Poirot\ApiClient\aClient;
use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\ApiClient\Request\Command;
use Poirot\Sms\Exceptions\exAuthentication;
use Poirot\Sms\Exceptions\exAuthNoCredit;
use Poirot\Sms\Exceptions\exMessageMalformed;
use Poirot\Sms\Exceptions\exMessaging;
use Poirot\Sms\Exceptions\exServerBan;
use Poirot\Sms\Exceptions\exServerError;
use Poirot\Sms\Interfaces\iClientOfSMS;
use Poirot\Sms\Interfaces\iMessage;
use Poirot\Sms\Interfaces\iSentMessage;
use Poirot\Sms\MelliPayamak\Soap\PlatformSoap;
use Poirot\Sms\SMSentMessage;


class Sms
    extends aClient
    implements iClientOfSMS
{
    protected $username;
    protected $password;
    protected $lineNumber = '10004908';


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
        $command = $this->_newCommand('SendSimpleSMS', array(
            'from'       => $this->lineNumber,
            'to'         => $recipients,
            'text'       => $message->getBody(),
            'isflash'    => $message->isFlash(),
        ));

        # Send Through Platform
        $res = $this->call($command);
        if ($ex = $res->hasException())
            throw $ex;

        # Message With Given UID from Server
        $res = $res->expected();
        $res = $res->SendSimpleSMSResult;

        $res = $res->string;
        switch ($res) {
            case 0: throw new exAuthentication;
                break;
            case 2: throw new exAuthNoCredit;
                break;
            case 3: throw new exServerBan('Maximum Daily Send Exceeded.');
                break;
            case 4: throw new exServerBan('The SMS is Very Long.');
                break;
            case 5: throw new exServerBan('Number is Invalid.');
                break;
            case 6: throw new exServerError;
                break;
            case 7: throw new exMessageMalformed('Contains Filtered Words.');
                break;
        }

        $rMessage = new SMSentMessage;
        $rMessage
            ->setUID($res)
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
        // Each Recipient has own message ID !!!
        $mUIDs = $message->getUID();

        $return = array();
        foreach ($message->getRecipients() as $i => $recipient) {
            # Make Command
            $command = $this->_newCommand('GetDelivery', array(
                'recId' => (string) $mUIDs[$i],
            ));

            # Send Through Platform
            $res = $this->call($command);
            if ($ex = $res->hasException())
                throw $ex;

            $res = $res->expected();
            $res = $res->GetDeliveryResult;

            switch($res) {
                case 0:   $status = iMessage::STATUS_PENDING;   break;
                case 1:   $status = iMessage::STATUS_DELIVERED; break;
                case 2:   $status = iMessage::STATUS_PENDING;   break;
                case 3:   $status = iMessage::STATUS_FAILED;    break;
                case 5:   $status = iMessage::STATUS_UNKNOWN;   break;
                case 8:   $status = iMessage::STATUS_SENT;      break;
                case 16:  $status = iMessage::STATUS_PENDING;   break;
                case 35:  $status = iMessage::STATUS_BANNED;    break;
                case 100: $status = iMessage::STATUS_UNKNOWN;   break;
                case 200: $status = iMessage::STATUS_SENT;      break;
                case 300: $status = iMessage::STATUS_BANNED;    break;
                case 400: $status = iMessage::STATUS_PENDING;   break;
                case 500: $status = iMessage::STATUS_FAILED;    break;
                default:  $status = iMessage::STATUS_UNKNOWN;
            }

            $return[$recipient] = $status;
        }

        return $return;
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
        # Make Command
        $conditions = array('Type' => 'All' /* | New | Count */);
        ($offset === null) ?: $conditions['LastMessageId‬‬'] = $offset;
        ($count   === null) ?: $conditions['Page‬‬'] = $count;
        $command = $this->_newCommand('GetMessages', array(
            'from'     => $this->lineNumber,
            'location' => 1, // received messages
            'index'    => ($offset === null) ? 0   : $offset,
            'count'    => ($count  === null) ? 100 : $count,
        ));

        # Send Through Platform
        $res = $this->call($command);
        if ($ex = $res->hasException())
            throw $ex;

        $return = array();

        $res  = $res->expected();
        $list = $res->getMessagesResult->MessagesBL;
        foreach ($list as $m) {
            $message = new SMSentMessage;
            $message
                ->setUID($m->MsgID)
                ->setBody($m->Body)
                ->setDateTimeCreated(new \DateTime($m->SendDate))
                ->setContributor($m->Sender)
                ->setFlash($m->IsFlash)
            ;

            $return[] = $message;
        }

        return $return;
    }

    /**
     * Count Total Message Inbox
     *
     * @return int
     */
    function getCountTotalInbox()
    {
        # Make Command
        $command = $this->_newCommand('GetInboxCount', array(
            'isRead' => false, // false: unread messages
        ));

        # Send Through Platform
        $res = $this->call($command);
        if ($ex = $res->hasException())
            throw $ex;

        $res = $res->expected();
        return $res->GetInboxCountResult;
    }

    /**
     * Get Remaining Account Credit
     *
     * @return int
     */
    function getRemainCredit()
    {
        # Make Command
        $command = $this->_newCommand('GetCredit');

        # Send Through Platform
        $res = $this->call($command);
        if ($ex = $res->hasException())
            throw $ex;

        $res    = $res->expected();
        $credit = $res->GetCreditResult;

        switch ($credit) {
            case 0: throw new exAuthentication;
        }

        return $credit;
    }


    // Options

    function setUsername($username)
    {
        $this->username = (string) $username;
        return $this;
    }

    function setPassword($password)
    {
        $this->password = (string) $password;
        return $this;
    }

    function setLineNumber($lineNumber)
    {
        $this->lineNumber = (string) $lineNumber;
        return $this;
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
            $this->platform = new PlatformSoap;

        return $this->platform;
    }


    // ..

    protected function _newCommand($methodName, array $args = null)
    {
        $method = new Command;
        $method->setMethod($methodName);
        ## account data options
        ## these arguments is mandatory on each call
        $defAccParams = array(
            'username' => $this->username,
            'password' => $this->password,
        );
        $args = ($args !== null) ? array_merge($defAccParams, $args) : $defAccParams;
        $method->setArguments($args);
        return $method;
    }
}