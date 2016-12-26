<?php
namespace Poirot\Sms\Interfaces;


interface iSentMessage
    extends iMessage,
            \Serializable
{
    /**
     * Set Receivers of message
     *
     * @param array $recipients
     *
     * @return $this
     */
    function setRecipients(array $recipients);

    /**
     * Get Message Recipients
     *
     * @return []string
     */
    function getRecipients();
}
