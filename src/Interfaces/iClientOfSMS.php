<?php
namespace Poirot\Sms\Interfaces;

use Poirot\Sms\Exceptions\exMessaging;


interface iClientOfSMS
{
    /**
     * Send Message To Recipients
     *
     * note: receptor_number can returned as a key for result
     *       [ $receptor => iSentMessage,  ..]
     *
     * @param array     $recipients  Receivers of message
     * @param iMessage  $message     Message
     *
     * @return iSentMessage[] Message(s) with given uid
     * @throws exMessaging
     */
    function sendTo(array $recipients, iMessage $message);

    /**
     * Get Message Delivery Status
     *
     * @param iSentMessage[] $messages
     *
     * @return array[$messageUid => iSMessage::STATUS_*]
     */
    function getMessageStatus(array $messages);

    /**
     * Get Inbox
     *
     * @param int $offset
     * @param int $limit
     *
     * @return mixed
     */
    function getInbox($offset = null, $limit = null);

    /**
     * Count Total Message Inbox
     *
     * @return int
     */
    function getCountTotalInbox();

    /**
     * Get Remaining Account Credit
     *
     * @return int
     */
    function getRemainCredit();
}
