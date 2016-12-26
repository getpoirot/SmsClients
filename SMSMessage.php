<?php
namespace Poirot\Sms;

use Poirot\Sms\Interfaces\iMessage;
use Poirot\Std\ConfigurableSetter;


class SMSMessage
    extends ConfigurableSetter
    implements iMessage
{
    protected $isFlash = false;
    protected $coding;
    protected $uid;
    protected $content = '';
    protected $dateTimeCreated;

    protected $_coding_available = array(
        iMessage::CODING_BINARY  => true,
        iMessage::CODING_DATA8   => true,
        iMessage::CODING_ISO     => true,
        iMessage::CODING_UNICODE => true,
    );

    protected $contentEncode;


    /**
     * Construct
     *
     * @param array|\Traversable $options
     */
    function __construct($options = null)
    {
        $this->dateTimeCreated = new \DateTime;
        parent::__construct($options);
    }

    /**
     * Set UID
     *
     * @param string|array $uid
     *
     * @return $this
     */
    function setUID($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * Get Message Unique ID
     *
     * @return string|array
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
        $this->contentEncode = null;
        return $this;
    }

    /**
     * Get Message Body
     *
     * @return string
     */
    function getBody()
    {
        if ($this->contentEncode)
            return $this->contentEncode;

        $coding = $this->_getPhpEncodingFrom($this->getCoding());
        $cntCod = $this->_getPhpEncodingFrom($this->_detectEncodingFromContent());
        switch ($coding) {
            case 'UTF-8':
            case 'ISO-8859-1':
                $this->contentEncode = iconv($cntCod, "{$coding}//TRANSLIT", $this->content);
                break;
            default:
                $this->contentEncode = $this->content;
        }

        return $this->contentEncode;
    }

    /**
     * Get Created DateTime
     *
     * @return \DateTime
     */
    function getCreatedDate()
    {
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
        $this->contentEncode = null;
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


    // ...

    function _detectEncodingFromContent()
    {
        $content = $this->content;
        if (empty($content) && ($content != '0' & $content != 0))
            return self::CODING_ISO;

        if ($this->_isBinary($content))
            return self::CODING_BINARY;

        $encoding = 'UTF-8';
        if (function_exists('mb_detect_encoding'))
            $encoding = mb_detect_encoding($content, "UTF-8, ISO-8859-1, WINDOWS-1252");

        switch ($encoding) {
            case 'UTF-8': $encoding = self::CODING_UNICODE;
                break;
            case 'ISO-8859-1':
            case 'WINDOWS-1252': $encoding = self::CODING_ISO;
                break;
        }

        return $encoding;
    }

    function _getPhpEncodingFrom($selfCodingName)
    {
        switch ($selfCodingName) {
            case self::CODING_ISO: $encode = 'ISO-8859-1';
                break;
            case self::CODING_UNICODE: $encode = 'UTF-8';
                break;
            default:
                $encode = self::CODING_BINARY;
        }

        return $encode;
    }

    function _isBinary($str)
    {
        return preg_match('~[^\x20-\x7E\t\r\n]~', $str) > 0;
    }
}
