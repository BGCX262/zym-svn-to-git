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
 * @version    $Id: Encrypt.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Filter_TwoWay
 */
require_once 'Zend/Filter/TwoWay.php';

/**
 * Encrypts a given string
 *
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_Encrypt extends Zend_Filter_TwoWay
{
    /**
     * Encryption adapter
     */
    protected $_adapter;

    /**
     * Class constructor
     *
     * @param string|array $options (Optional) Options to set, if null mcrypt is used
     */
    public function __construct($options = null)
    {
        parent::_setDefaultNamespace('Zend_Filter_Encrypt');
        parent::_setDefaultAdapter('Mcrypt');
        parent::__construct($options);
    }

    /**
     * Encrypts the content $value with the defined settings
     *
     * @param  string $value Content to encrypt
     * @return string The encrypted content
     */
    public function filterTo($value)
    {
        return $this->_adapter->encrypt($value);
    }

    /**
     * Decrypts the content $value with the defined settings
     *
     * @param  string $value Content to decrypt
     * @return string The decrypted content
     */
    public function filterFrom($value)
    {
        return $this->_adapter->decrypt($value);
    }

    /**
     * Encrypts the content $value with the defined settings
     *
     * @param  string $value Content to encrypt
     * @return string The encrypted content
     */
    public function filter($value)
    {
        return $this->_adapter->encrypt($value);
    }
}
