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
 * @package    Zend_Environment
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Exception.php 2794 2007-01-16 01:29:51Z bkarwin $
 */


/**
 * Zend_Environment_Exception
 */
require_once 'Zend/Environment/Exception.php';


/**
 * Zend_Environment_Field
 */
require_once 'Zend/Environment/Field.php';


/**
 * @category   Zend
 * @package    Zend_Environment
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Environment_Security_Field extends Zend_Environment_Field
{

    /**
     * Default properties for a field
     *
     * @var array
     */
    protected $_data = array('group' =>   null,
    'name' =>    null,
    'result_code' =>  null,
    'result' =>  null,
    'details' => null,
    'current_value' => null,
    'recommended_value' => null,
    'link' =>    null);

}
