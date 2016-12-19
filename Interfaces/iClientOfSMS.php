<?php
namespace Poirot\Sms;


use Poirot\Sms\Exceptions\exMessaging;

interface iClientOfSMS
{
    /**
     * Send Message To Recipients
     *
     * @param array     $recipients  Receivers of message
     * @param iSMessage $message     Message
     *
     * @return iSMessage Message with given uid
     * @throws exMessaging
     */
    function sendTo(array $recipients, iSMessage $message);

    /**
     * Get Message Delivery Status
     *
     * @param string $messageUid
     *
     * @return string
     */
    function getMessageStatus($messageUid);

    /**
     * List All Sent Messages
     *
     * !! usually providers list messages for current date only
     *
     * @param int $limit
     *
     * @return  []iSMessage
     */
    function listSentMessages($limit);

    /**
     * Get Remaining Account Credit
     *
     * @return int
     */
    function getRemainCredit();

    /**
     * Get Messaging Line Number
     *
     * @return string
     */
    function getLineNumber();

}
