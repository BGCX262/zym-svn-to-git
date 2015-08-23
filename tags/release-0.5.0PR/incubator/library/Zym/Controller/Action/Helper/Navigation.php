<?php
/**
 * Zym Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Zym
 * @package    Zym_Controller
 * @subpackage Action_Helper
 * @copyright  Copyright (c) 2008 Zym. (http://www.zym-project.com/)
 * @license    http://www.zym-project.com/license    New BSD License
 */

/**
 * @see Zend_Controller_Action_Helper_Abstract
 */
require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * @see Zym_Navigation
 */
require_once 'Zym/Navigation.php';

/**
 * Helper for Zym_Navigation
 * 
 * @category   Zym
 * @package    Zym_Controller
 * @subpackage Action_Helper
 * @author     Robin Skoglund
 * @copyright  Copyright (c) 2008 Zym. (http://www.zym-project.com/)
 * @license    http://www.zym-project.com/license    New BSD License
 */
class Zym_Controller_Action_Helper_Navigation
    extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Navigation container to operate on
     *
     * @var Zym_Navigation_Container
     */
    protected $_container;
    
    /**
     * Creates navigation helper
     *
     */
    public function __construct()
    {
        $this->init();
    }
    
    /**
     * Api for extending classes
     *
     * @return void
     */
    public function init()
    {
        
    }
    
    /**
     * Sets navigation container to operate on
     *
     * @param  Zym_Navigation_Container $container  container to operate on
     * @return void
     */
    public function setNavigation(Zym_Navigation_Container $container)
    {
        $this->_container = $container;
    }
    
    /**
     * Returns navigation container
     *
     * @return Zym_Navigation_Container
     */
    public function getNavigation()
    {
        if (null === $this->_container) {
            
        }
        
        return $this->_container;
    }
    
    /**
     * Returns default navigation container
     *
     * @return Zym_Navigation_Container
     */
    protected function _getDefaultNavigation()
    {
        require_once 'Zend/Registry.php';
        if (Zend_Registry::isRegistered('Zym_Navigation')) {
            $nav = Zend_Registry::get('Zym_Navigation');
            if ($nav instanceof Zym_Navigation_Container) {
                return $nav;
            }
        }
    }
    
    /**
     * Returns navigation container
     *
     * @return Zym_Navigation_Container
     */
    public function direct()
    {
        return $this->getNavigation();
    }
}
