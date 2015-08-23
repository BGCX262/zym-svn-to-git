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
 * @see Zend_Filter_Encode_EncodeInterface
 */
require_once 'Zend/Filter/Encode/EncodeInterface.php';

/**
 * @see Zend_Filter_TwoWay_TwoWayInterface
 */
require_once 'Zend/Filter/TwoWay/TwoWayInterface.php';

/**
 * Interface for two-way filters
 *
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Filter_Encode_AbstractEncode implements Zend_Filter_Encode_EncodeInterface, Zend_Filter_TwoWay_TwoWayInterface
{
    /**
     * Returns the encoded value
     *
     * @param  mixed $value Content to be encoded
     * @throws Zend_Filter_Exception When filtering is not possible
     * @return mixed
     */
    public function filterTo($value)
    {
        return $this->encode($value);
    }

    /**
     * Returns the decoded value
     *
     * @param  mixed $value Content to be decoded
     * @throws Zend_Filter_Exception When filtering is not possible
     * @return mixed
     */
    public function filterFrom($value)
    {
        return $this->decode($value);
    }
}
