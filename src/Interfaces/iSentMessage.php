<?php
namespace Poirot\Sms\Interfaces;


interface iSentMessage
    extends iMessage,
            \Serializable
{
    /*
     * ++ Message Status ++
     */
    const STATUS_SENT      = 'stat.sent';
    const STATUS_PENDING   = 'stat.pending';
    const STATUS_DELIVERED = 'stat.delivered';
    const STATUS_FAILED    = 'stat.failed';
    const STATUS_UNKNOWN   = 'stat.unknown';
    const STATUS_BANNED    = 'stat.banned';
    const STATUS_NOTSENT   = 'stat.notsent';


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


    /**
     * Set Message Contributor
     *
     * @param string $from
     *
     * @return $this
     */
    function setContributor($from);

    /**
     * Get Message Contributor
     *
     * @return null|string
     */
    function getContributor();


    /**
     * Set Sent Message Status
     *
     * @param string $status
     *
     * @return $this
     */
    function setStatus($status);

    /**
     * Get Message Status
     *
     * @return string
     */
    function getStatus();
}
