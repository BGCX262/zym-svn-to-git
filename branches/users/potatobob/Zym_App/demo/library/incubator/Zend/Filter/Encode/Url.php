<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Mcrypt.php 21212 2010-02-27 17:33:27Z thomas $
 */

/**
 * @see Zend_Filter_Encode_AbstractEncode
 */
require_once 'Zend/Filter/Encode/AbstractEncode.php';

/**
 * Encoding adapter for Url
 *
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_Encode_Url extends Zend_Filter_Encode_AbstractEncode
{
    /**
     * Using raw mode?
     *
     * @var boolean
     */
    protected $_raw = false;

    /**
     * Returns the actual set RAW mode
     *
     * @return boolean
     */
    public function getRaw()
    {
        return $this->_raw;
    }

    /**
     * Set or unsets the RAW mode
     *
     * @param boolean $mode TRUE = raw mode
     * @return Zend_Filter_Encode_Url
     */
    public function setRaw($mode)
    {
        $this->_raw = (boolean) $mode;
        return $this;
    }

    /**
     * Encodes $value with the defined settings
     *
     * @param  string $value The content to encode
     * @return string The encoded content
     */
    public function encode($value)
    {
        if ($this->getRaw()) {
            return rawurlencode($value);
        }

        return urlencode($value);
    }

    /**
     * Decodes $value with the defined settings
     *
     * @param  string $value Content to decode
     * @return string The decoded content
     */
    public function decode($value)
    {
        if ($this->getRaw()) {
            return rawurldecode($value);
        }

        return urldecode($value);
    }

    /**
     * Returns the adapter name
     *
     * @return string
     */
    public function toString()
    {
        return 'Url';
    }
}
