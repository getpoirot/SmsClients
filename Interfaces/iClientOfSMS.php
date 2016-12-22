<?php
namespace Poirot\Sms\Interfaces;

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
     * @return array[$recipient_number => iSMessage::STATUS_*]
     */
    function getMessageStatus($messageUid);

    /**
     * Get Remaining Account Credit
     *
     * @return int
     */
    function getRemainCredit();
}
