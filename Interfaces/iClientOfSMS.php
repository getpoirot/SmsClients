<?php
namespace Poirot\Sms\Interfaces;

use Poirot\Sms\Exceptions\exMessaging;


interface iClientOfSMS
{
    /**
     * Send Message To Recipients
     *
     * @param array     $recipients  Receivers of message
     * @param iMessage $message     Message
     *
     * @return iSentMessage Message with given uid
     * @throws exMessaging
     */
    function sendTo(array $recipients, iMessage $message);

    /**
     * Get Message Delivery Status
     *
     * @param iSentMessage $message
     *
     * @return array[$recipient_number => iSMessage::STATUS_*]
     */
    function getMessageStatus(iSentMessage $message);

    /**
     * Get Remaining Account Credit
     *
     * @return int
     */
    function getRemainCredit();
}
