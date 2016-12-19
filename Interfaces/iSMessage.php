<?php
namespace Poirot\Sms;

/**
 * Length of standard Message with Characters include:
 *   unicode(‫‪UCS2)‬  is 70  chrs.
 *   ascii(DEFAULT) is 160 chrs.
 *   binary(‫‪DATA_8) is 140 byte
 *
 * in messages with longer length
 *   unicode 3 chrs. assign to UDH message header
 *   ascii   7 chrs.
 *
 */
interface iSMessage
    extends \Serializable
{
    /*
     * ++ Message Coding ++
     */
    const CODING_ASCII   = 'ASCII';
    const CODING_UNICODE = 'UCS2';
    const CODING_DATA8   = 'DATA_8';
    const CODING_BINARY  = 'BINARY';

    /*
     * ++ Message Status ++
     */
    const STATUS_SENT      = 'stat.sent';
    const STATUS_PENDING   = 'stat.pending';
    const STATUS_DELIVERED = 'stat.delivered';
    const STATUS_FAILED    = 'stat.failed';


    /**
     * Get Message Unique ID
     *
     * @return string
     */
    function getUID();

    /**
     * Get Message Body
     *
     * @return string
     */
    function getBody();


    /**
     * Get Created DateTime
     *
     * @return \DateTime
     */
    function getCreatedDate();


    /**
     * Set Message Coding
     *
     * @param string $coding
     *
     * @return $this
     */
    function setCoding($coding);

    /**
     * Get Coding
     *
     * @return string
     */
    function getCoding();

    /**
     * Set Message Type To Flash Message
     *
     * @return $this
     */
    function setFlash();

    /**
     * Is Flash Message?
     *
     * @return boolean
     */
    function isFlash();

}
