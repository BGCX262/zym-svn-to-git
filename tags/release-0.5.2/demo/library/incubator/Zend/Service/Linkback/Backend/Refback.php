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
 * @package    Zend_Service_Linkback
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */ 

/**
 * Refback backend
 *
 * @category   Zend
 * @package    Zend_Service_Linkback
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Linkback_Backend_Refback
      implements Zend_Service_Linkback_Backend_BackendInterface
{
    /**
     * Handle a linkback request
     * 
     * @param  Zend_Controller_Request_Http $request
     * @return void
     */
    public function handleRequest(Zend_Controller_Request_Http $request)
    {
        
    }
    
    /**
     * Register the appropriate headers in a http response, and view object.
     * 
     * @param Zend_Controller_Response_Http $response
     * @param Zend_View_Abstract            $view
     * @return void
     */
    public function registerHeaders(Zend_Controller_Response_Http $response,
                                    Zend_View_Abstract $view)
    {
                                        
    }
         
}