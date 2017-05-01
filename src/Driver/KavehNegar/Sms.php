<?php
namespace Poirot\Sms\Driver\KavehNegar;

use Poirot\ApiClient\aClient;
use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\ApiClient\Request\Command;
use Poirot\Sms\Exceptions\exMessageMalformed;
use Poirot\Sms\Exceptions\exMessaging;
use Poirot\Sms\Interfaces\iClientOfSMS;
use Poirot\Sms\Interfaces\iMessage;
use Poirot\Sms\Interfaces\iSentMessage;
use Poirot\Sms\Driver\KavehNegar\Rest\PlatformRest;
use Poirot\Sms\Entity\SMSentMessage;
use Poirot\Std\Struct\DataEntity;

/*
$sms = new Poirot\Sms\Driver\KavehNegar\Sms([
    'api_key'     => '7136787A396169757A7A6D4D714B44343330336F67773D3D',
    'sender_line' => '10007770070777'
]);
*/

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
            'localid'  => $message->getUid(),
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
                ->setUid($entry['messageid'])
                ->setBody($entry['message'])
                ->setContributor($entry['sender'])
                ->setRecipients([$entry['receptor']])
                ->setStatus($this->_transMessageStatus($entry['status']))
                ->setFlash($message->isFlash())
                ->setCoding($message->getCoding())
                ->setDateTimeCreated(new \DateTime( date("c", $entry['date']) ))
            ;

            $result[] = $rMessage;
        }

        return $result;
    }

    /**
     * Send Verification Message To Receptor
     *
     * @param string $receptor Receiver of message
     * @param array  $tokens   ['token' => (string), ...]
     *
     * @return iSentMessage[] Message(s) with given uid
     * @throws exMessaging
     */
    function sendVerificationTo($receptor, $template, array $tokens)
    {
        # Validate Given Recipients Mobile Number
        if (! \Poirot\Sms\isValidMobileNum($receptor))
            throw new exMessageMalformed(sprintf(
                'Invalid Recipient Phone Number for (%s).'
                , $receptor
            ));


        # Make Command
        $args = array(
            'receptor' => $receptor,
            'template' => $template,
            'type'     => 'sms',
        ) + $tokens;

        $command = $this->_newCommand('Lookup', $args);

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
                ->setUid($entry['messageid'])
                ->setBody($entry['message'])
                ->setContributor($entry['sender'])
                ->setRecipients([$entry['receptor']])
                ->setStatus($this->_transMessageStatus($entry['status']))
                ->setDateTimeCreated(new \DateTime( date("c", $entry['date']) ))
            ;

            $result[] = $rMessage;
        }

        return $result;
    }

    /**
     * Get Message Delivery Status
     *
     * @param iSentMessage[] $messages
     *
     * @return array [$messageUid => iSMessage::STATUS_*]
     * @throws \Exception
     */
    function getMessageStatus(array $messages)
    {
        $messageIds = [];
        foreach ($messages as $m) {
            if (! $mId = $m->getUid() )
                throw new \Exception('Message has no Unique Identifier.');

            $messageIds[] = $mId;
        }



        # Make Command
        $command = $this->_newCommand('Status', array(
            'messageid' => implode(', ', $messageIds),
        ));

        # Send Through Platform
        $apiResponse = $this->call($command);
        if ($ex = $apiResponse->hasException())
            throw $ex;


        # Build Result
        /** @var DataEntity $apiResponse */
        $apiResponse = $apiResponse->expected();

        $result = [];
        foreach ($apiResponse->get('entries') as $entry) {
            $result[$entry['messageid']] = $this->_transMessageStatus($entry['status']);
        }

        return $result;
    }

    /**
     * Find Sent Message With Given ID
     *
     * @param mixed[] $messageIds
     *
     * @return iSentMessage[]
     */
    function getSentMessageWithId(array $messageIds)
    {
        # Make Command
        $command = $this->_newCommand('Select', array(
            'messageid' => implode(', ', $messageIds),
        ));

        # Send Through Platform
        $apiResponse = $this->call($command);
        if ($ex = $apiResponse->hasException())
            throw $ex;


        # Build Result
        /** @var DataEntity $apiResponse */
        $apiResponse = $apiResponse->expected();

        $result = [];
        foreach ($apiResponse->get('entries') as $entry) {
            $rMessage = new SMSentMessage;
            $rMessage
                ->setUid($entry['messageid'])
                ->setBody($entry['message'])
                ->setContributor($entry['sender'])
                ->setRecipients([$entry['receptor']])
                ->setStatus($this->_transMessageStatus($entry['status']))
                ->setDateTimeCreated(new \DateTime( date("c", $entry['date']) ))
            ;

            $result[] = $rMessage;
        }

        return $result;
    }

    /**
     * Get Inbox
     *
     * Max Result is 100, so it can be sent while the
     * result is equal to 100 to fetch all messages.
     *
     * @param int $offset
     * @param int $limit
     *
     * @return mixed
     */
    function getInbox($offset = null, $limit = null)
    {
        # Make Command
        $command = $this->_newCommand('Receive', array(
            'linenumber' => $this->sender, // it must be not null
            'isread'     => 1,
        ));

        # Send Through Platform
        $apiResponse = $this->call($command);
        if ($ex = $apiResponse->hasException())
            throw $ex;


        # Build Result
        /** @var DataEntity $apiResponse */
        $apiResponse = $apiResponse->expected();

        $result = [];
        foreach ($apiResponse->get('entries') as $entry) {
            $rMessage = new SMSentMessage;
            $rMessage
                ->setUid($entry['messageid'])
                ->setBody($entry['message'])
                ->setContributor($entry['sender'])
                ->setRecipients([$entry['receptor']])
                ->setStatus(iSentMessage::STATUS_DELIVERED)
                ->setDateTimeCreated(new \DateTime( date("c", $entry['date']) ))
            ;

            $result[] = $rMessage;
        }

        return $result;
    }

    /**
     * Count Total Message Inbox
     *
     * @return int
     */
    function getCountTotalInbox()
    {
        # Make Command
        $dtNow      = new \DateTime();
        $beginOfDay = clone $dtNow;
        $beginOfDay->modify('today');
        $endOfDay   = clone $beginOfDay;
        $endOfDay->modify('tomorrow');


        $command = $this->_newCommand('CountInbox', array(
            'startdate'  => $beginOfDay->getTimestamp(),
            'enddate'    => $endOfDay->getTimestamp(),
            'linenumber' => $this->sender,
            'isread'     => 0,
        ));

        # Send Through Platform
        $apiResponse = $this->call($command);
        if ($ex = $apiResponse->hasException())
            throw $ex;


        # Build Result
        /** @var DataEntity $apiResponse */
        $apiResponse = $apiResponse->expected();

        $result = 0;
        foreach ($apiResponse->get('entries') as $entry) {
            $result += $entry['sumcount'];
        }

        return $result;
    }

    /**
     * Get Remaining Account Credit
     *
     * @return int
     */
    function getRemainCredit()
    {
        $command = $this->_newCommand('Info');

        # Send Through Platform
        $apiResponse = $this->call($command);
        if ($ex = $apiResponse->hasException())
            throw $ex;


        # Build Result
        /** @var DataEntity $apiResponse */
        $apiResponse = $apiResponse->expected();

        $entries = $apiResponse->get('entries');
        return $entries['remaincredit'];
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
        $method->setMethodName($methodName);
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
                $return = iSentMessage::STATUS_INVALID; break;
        }

        return $return;
    }
}
