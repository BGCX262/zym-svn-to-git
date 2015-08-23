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
 * @package    Zend_Oauth
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Server.php 20217 2010-01-12 16:01:57Z matthew $
 */

/** Zend_Oauth */
require_once 'Zend/Oauth.php';

/** Zend_Uri */
require_once 'Zend/Uri.php';

/** Zend_Oauth_Config */
require_once 'Zend/Oauth/Config.php';

/**
 * @category   Zend
 * @package    Zend_Oauth
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Oauth_Server extends Zend_Oauth implements Zend_Oauth_Config_ConfigInterface
{
    /**
     * @var Zend_Oauth_Config
     */
    protected $_config = null;

    /**
     * Constructor
     * 
     * @param  null|array|Zend_Config $options 
     * @return void
     */
    public function __construct($options = null)
    {
        $this->_config = new Zend_Oauth_Config;
        if (!is_null($options)) {
            if ($options instanceof Zend_Config) {
                $options = $options->toArray();
            }
            $this->_config->setOptions($options);
        }
    }

    /**
     * Create request token
     * 
     * @return Zend_Oauth_Server
     * @throws Zend_Oauth_Exception for unsupported methods
     */
    public function createRequestToken()
    {
        if (strtoupper($_SERVER['REQUEST_METHOD']) == Zend_Oauth::POST) {
            
        } elseif (strtoupper($_SERVER['REQUEST_METHOD']) == Zend_Oauth::GET) {

        }

        require_once 'Zend/Oauth/Exception.php';
        throw new Zend_Oauth_Exception('Unsupported method: '
            . strtoupper($_SERVER['REQUEST_METHOD']));

        return $this;
    }

    /**
     * Create access token
     * 
     * @return Zend_Oauth_Server
     */
    public function createAccessToken() 
    {
    }

    /**
     * Get redirect URL
     * 
     * @return void
     */
    public function getRedirectUrl() 
    {
    }

    /**
     * Redirect
     * 
     * @return void
     */
    public function redirect() 
    {
    }

    /**
     * Whether or not the request token is authorized
     * 
     * @return boolean
     */
    public function isAuthorized() 
    {
    }

    /**
     * Proxy calls to config object
     * 
     * @param  string $method 
     * @param  array $args 
     * @return mixed
     */
    public function __call($method, array $args) 
    {
        if (!method_exists($this->_config, $method)) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception('Method does not exist: ' . $method);
        }
        return call_user_func_array(array($this->_config, $method), $args);
    }
}
