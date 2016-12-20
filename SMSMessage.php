<?php
namespace Poirot\Sms;

use Poirot\Std\ConfigurableSetter;


class SMSMessage
    extends ConfigurableSetter
    implements iSMessage
{
    protected $isFlash = false;
    protected $coding;
    protected $uid;
    protected $content = '';
    protected $dateTimeCreated;

    protected $_coding_available = array(
        iSMessage::CODING_ASCII   => true,
        iSMessage::CODING_BINARY  => true,
        iSMessage::CODING_DATA8   => true,
        iSMessage::CODING_UNICODE => true,
    );


    /**
     * Set UID
     *
     * @param string $uid
     *
     * @return $this
     */
    function setUID($uid)
    {
        $this->uid = (string) $uid;
        return $this;
    }

    /**
     * Get Message Unique ID
     *
     * @return string
     */
    function getUID()
    {
        if ($this->uid === null)
            $this->setUID(bin2hex(random_bytes(8)));

        return $this->uid;
    }

    /**
     * Set Content Body
     * @param string $content
     * @return $this
     */
    function setBody($content)
    {
        $this->content = (string) $content;
        return $this;
    }

    /**
     * Get Message Body
     *
     * @return string
     */
    function getBody()
    {
        return $this->content;
    }

    /**
     * Get Created DateTime
     *
     * @return \DateTime
     */
    function getCreatedDate()
    {
        if (!$this->dateTimeCreated)
            $this->dateTimeCreated = new \DateTime;

        return $this->dateTimeCreated;
    }

    /**
     * Set Message Coding
     *
     * @param string $coding
     *
     * @return $this
     */
    function setCoding($coding)
    {
        $coding = (string) $coding;
        if (!isset($this->_coding_available[$coding]))
            throw new \InvalidArgumentException(sprintf(
                'Coding (%s) is not valid.'
                , $coding
            ));

        $this->coding = $coding;
        return $this;
    }

    /**
     * Get Coding
     * ! coding may change when body set
     *
     * @return string
     */
    function getCoding()
    {
        if ($this->coding === null)
            $this->setCoding($this->_detectEncodingFromContent());

        return $this->coding;
    }

    /**
     * Set Message Type To Flash Message
     *
     * @param bool $bool
     *
     * @return $this
     */
    function setFlash($bool = true)
    {
        $this->isFlash = (boolean) $bool;
        return $this;
    }

    /**
     * Is Flash Message?
     *
     * @return boolean
     */
    function isFlash()
    {
        return $this->isFlash;
    }


    // Implement Serializable

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    function serialize()
    {
        // TODO: Implement serialize() method.
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    function unserialize($serialized)
    {
        // TODO: Implement unserialize() method.
    }


    // ...

    function _detectEncodingFromContent()
    {
        // TODO Implement this
        return iSMessage::CODING_ASCII;
    }
}
