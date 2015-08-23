<?php
/**
 * Zym Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category Zym
 * @package Zym_App
 * @subpackage Resource
 * @copyright  Copyright (c) 2008 Zym. (http://www.zym-project.com/)
 * @license http://www.zym-project.com/license New BSD License
 */

/**
 * @see Zym_App_Resource_Abstract
 */
require_once 'Zym/App/Resource/Abstract.php';

/**
 * @see Zend_Loader
 */
require_once 'Zend/Loader.php';

/**
 * Registers class autoloader
 *
 * @author Geoffrey Tran
 * @license http://www.zym-project.com/license New BSD License
 * @category Zym
 * @package Zym_App
 * @subpackage Resource
 * @copyright  Copyright (c) 2008 Zym. (http://www.zym-project.com/)
 */
class Zym_App_Resource_Loader extends Zym_App_Resource_Abstract
{
    /**
     * Resource Dependencies on other resources
     *
     * @var array
     */
    protected $_dependencies = array('Php');

    /**
     * Dispatching event
     *
     * This resource will dispatch right after it is constructed
     *
     * @var string
     */
    protected $_dispatchEvent = 'afterResourceLoaded';

    /**
     * Default Config
     *
     * @var array
     */
    protected $_defaultConfig = array(
        Zym_App::ENV_PRODUCTION => array(
            'plugin_loader' => array(
                'enabled' => true
            )
        ),

        Zym_App::ENV_DEFAULT => array(
            'autoload' => array(
                'enabled' => true,
                'class'   => 'Zend_Loader'
            ),
            'plugin_loader' => array(
                'enabled'            => false,
                'include_file_cache' => 'cache/PluginLoaderCache.php', // relative to PATH_DATA dir
             )
        )
    );

    /**
     * Setup
     *
     * @param Zend_Config $config
     */
    public function setup(Zend_Config $config)
    {
        $this->_setupAutoloader($config->get('autoload'));
        $this->_setupPluginLoader($config->get('plugin_loader'));
    }

    /**
     * Setup autoloading
     *
     * @param Zend_Config $config
     * @return void
     */
    public function _setupAutoloader(Zend_Config $config)
    {
        if (!$config->get('enabled')) {
            return;
        }

        // Use non-default autoload function?
        $class = $config->get('class');

        // Allow loading multiple loaders
        if ($class instanceof Zend_Config) {
            $classes = $class->toArray();
        } else {
            $classes = (array) $class;
        }

        // Register autoload
        foreach (array_reverse($classes) as $loader) {
            Zend_Loader::registerAutoload($loader);
        }
    }

    /**
     * Setup plugin loader
     *
     * @param Zend_Config $config
     * @return void
     */
    protected function _setupPluginLoader(Zend_Config $config)
    {
        $file              = $config->get('include_file_cache');
        $classFileIncCache = $this->getApp()->getPath(Zym_App::PATH_DATA, $file);

        if (! $config->get('use_file_cache')) {
            // Delete existing file cache
            @unlink($classFileIncCache);

            return;
        }

        if (file_exists($classFileIncCache)) {
            include_once $classFileIncCache;
        }

        /**
         * @see Zend_Loader_PluginLoader
         */
        require_once 'Zend/Loader/PluginLoader.php';
        Zend_Loader_PluginLoader::setIncludeFileCache($classFileIncCache);
    }
}