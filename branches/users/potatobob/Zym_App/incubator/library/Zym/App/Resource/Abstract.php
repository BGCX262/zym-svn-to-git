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
 * @see Zym_App
 */
require_once 'Zym/App.php';

/**
 * @see Zend_Config
 */
require_once'Zend/Config.php';

/**
 * @see Zym_Dependency_Item_Interface
 */
require_once 'Zym/Dependency/Item/Interface.php';

/**
 * Abstract resource class
 *
 * @author Geoffrey Tran
 * @license http://www.zym-project.com/license New BSD License
 * @category Zym
 * @package Zym_App
 * @subpackage Resource
 * @copyright  Copyright (c) 2008 Zym. (http://www.zym-project.com/)
 */
abstract class Zym_App_Resource_Abstract implements Zym_Dependency_Item_Interface
{
    /**
     * Resource ID
     *
     * @var string
     */
    protected $_id;

    /**
     * Array of dependency objects
     *
     * @var array
     */
    protected $_dependencies = array();

    /**
     * Application
     *
     * @var Zym_App
     */
    private $_app;

    /**
     * Cloned cache object
     *
     * @var Zend_Cache_Core
     */
    private $_cache;

    /**
     * Event to listen for to dispatch
     *
     * Pass an array to specify event name and call back
     * <code>array('dispatch', 'notify')</code>
     *
     * @var mixed
     */
    protected $_dispatchEvent;

    /**
     * Zend_Config
     *
     * @var Zend_Config
     */
    private $_config;

    /**
     * Default config
     *
     * @var array
     */
    protected $_defaultConfig = array(
        Zym_App::ENV_DEVELOPMENT => array(),
        Zym_App::ENV_PRODUCTION  => array(),
        Zym_App::ENV_TEST        => array(),
        Zym_App::ENV_CLI         => array(),
        Zym_App::ENV_DEFAULT     => array()
    );

    /**
     * Default config object cache
     *
     * @var Zend_Config
     */
    private $_defaultConfigObject;

    /**
     * Environment
     *
     * @var string
     */
    protected $_environment = Zym_App::ENV_PRODUCTION;

    /**
     * Whether resource was dispatched
     *
     * @var boolean
     */
    protected $_dispatched;

    /**
     * Construct
     *
     * @return void
     */
    public function __construct(Zym_App $app, Zend_Config $config = null, $environment = null)
    {
        $dispatcher = $app->getMessageDispatcher();
        $dispatcher->post('beforeResourceLoaded', $this);

        // Set app
        $this->setApp($app);

        // Cache
        $this->setCache($app->getCache());

        // Set config
        if ($config) {
            if ($environment === null) {
                $environment = $this->getEnvironment();
            }

            $this->setConfig($config, $environment);
        }


        // Extension api
        $this->init($this->getConfig());

        // Setup listener to dispatch resource
        if (isset($this->_dispatchEvent)) {
            $event    = (is_array($this->_dispatchEvent) && isset($this->_dispatchEvent[0]))
                            ? $this->_dispatchEvent[0] : $this->_dispatchEvent;
            $callback = (is_array($this->_dispatchEvent) && isset($this->_dispatchEvent[1]))
                            ? $this->_dispatchEvent[1] : 'dispatch';
            $dispatcher->attach($this, $event, $callback);
        }

        $dispatcher->post('afterResourceLoaded', $this);
    }

    /**
     * Init
     *
     * If you intend to modify the config, do it here so it can
     * be cached automatically.
     *
     * Do not setup the bootstrap here as it will not have effect when
     * caching is enabled.
     *
     * @param Zend_Config $config
     * @return void
     */
    public function init(Zend_Config $config)
    {}

    /**
     * Dispatch the setup process
     *
     * @return void
     */
    public function dispatch()
    {
        if ($this->_dispatched) {
            return;
        }

        $config     = $this->getConfig();
        $dispatcher = $this->getApp()->getMessageDispatcher();

        $dispatcher->post('beforeResourceSetup', $this);
        $this->setup($config);
        $dispatcher->post('afterResourceSetup', $this);

        // Detach from dispatch event
        if (isset($this->_dispatchEvent)) {
            $event = (is_array($this->_dispatchEvent) && isset($this->_dispatchEvent[0]))
                        ? $this->_dispatchEvent[0] : $this->_dispatchEvent;
            $dispatcher->detach($this, $event);
        }

        $this->_dispatched = true;
    }

    /**
     * Sets up the resource
     *
     */
    public function setup(Zend_Config $config)
    {}

    /**
     * Return a Zend_Config object populated with appropriate properties and
     * reasonable default values for this resource type.
     *
     * @param string $environment Environment to retrieve from merged with default config
     * @return Zend_Config
     */
    public function getDefaultConfig($environment = null)
    {
        // Cache config obj
        if (!$this->_defaultConfigObject instanceof Zend_Config) {
            // Config array
            $config = array();

            // Get environment config
            if (isset($this->_defaultConfig[$environment])) {
                $config = $this->_defaultConfig[$environment];
            }

            // Ensure default environment exists
            if (isset($this->_defaultConfig[Zym_App::ENV_DEFAULT])) {
                $config = $this->_arrayMergeRecursiveOverwrite($this->_defaultConfig[Zym_App::ENV_DEFAULT], $config);
            }

            $this->_defaultConfigObject = new Zend_Config($config);
        }

        return $this->_defaultConfigObject;
    }


    /**
     * Config
     *
     * @return Zend_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Set resource config
     *
     * @param Zend_Config $config
     * @return Zym_App_Resource_Abstract
     */
    public function setConfig(Zend_Config $config, $environment = null)
    {
        // Merge default config with user config
        $defaultConfig = $this->getDefaultConfig($environment);
        $this->_config = $this->_mergeConfig($defaultConfig, $config);


        return $this;
    }

    /**
     * Get Application
     *
     * @return Zym_App
     */
    public function getApp()
    {
        return $this->_app;
    }

    /**
     * Set Application
     *
     * @param Zym_App $application
     * @return Zym_App_Resource_Abstract
     */
    public function setApp(Zym_App $application)
    {
        $this->_app = $application;
        return $this;
    }

    /**
     * Get environment
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->_environment;
    }

    /**
     * Get cache object
     *
     * @param string $id
     * @return Zend_Cache_Core|mixed
     */
    public function getCache($id = null)
    {
        if ($id !== null) {
            return $this->_cache->load($id);
        }

        return $this->_cache;
    }

    /**
     * Set cache object
     *
     * @param Zend_Cache_Core $cache
     */
    public function setCache(Zend_Cache_Core $cache)
    {
        $prefix = $cache->getOption('cache_id_prefix');

        $cache = clone $cache;
        $cache->setOption('automatic_serialization', true);
        $cache->setOption('cache_id_prefix', $prefix . get_class($this) . '__');

        $this->_cache = $cache;

        return $this;
    }

    /**
     * Set item identifier
     *
     * @param string $id
     * @return Zym_Dependency_Item
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * Get item identifier
     *
     * @return string
     */
    public function getId()
    {
        // Shitty way to make an ID
        if ($this->_id === null) {
            $fullClassName = get_class($this);

            if (strpos($fullClassName, '_') !== false) {
                $name = strrchr($fullClassName, '_');
                $this->_id = ltrim($name, '_');
            } else {
                $this->_id = $fullClassName;
            }
        }

        return $this->_id;
    }

    /**
     * Set dependencies
     *
     * @param array $depends
     * @return Zym_Dependency_Item
     */
    public function setDependencies(array $depends)
    {
        $this->_dependencies = $depends;
        return $this;
    }

    /**
     * Get dependencies
     *
     * @return array
     */
    public function getDependencies()
    {
        return $this->_dependencies;
    }

    /**
     * Merge two arrays recursively, overwriting keys of the same name name
     * in $array1 with the value in $array2.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    protected function _arrayMergeRecursiveOverwrite($array1, $array2)
    {
        if (is_array($array1) && is_array($array2)) {
            foreach ($array2 as $key => $value) {
                if (isset($array1[$key])) {
                    $array1[$key] = $this->_arrayMergeRecursiveOverwrite($array1[$key], $value);
                } else {
                    $array1[$key] = $value;
                }
            }
        } else {
            if (is_array($array1) && trim($array2) === '') {
                return $array1;
            }

            $array1 = $array2;
        }

        return $array1;
    }

    /**
     * A simple way to merge config sections and get a Zend_Config object
     *
     * @param Zend_Config|array $configA
     * @param Zend_Config|array $configB
     * @return Zend_Config
     */
    protected function _mergeConfig($configA, $configB)
    {
        /* We can't do this because there is no way to make sure it's a writable config obj
        // Use Zend_Config's merge
        if ($configA instanceof Zend_Config && $configB instanceof Zend_Config) {
            $configA->merge($configB);
            return $configA;
        }
        */

        // Convert to array
        $configA = ($configA instanceof Zend_Config) ? $configA->toArray() : (array) $configA;
        $configB = ($configB instanceof Zend_Config) ? $configB->toArray() : (array) $configB;

        $newConfig = $this->_arrayMergeRecursiveOverwrite($configA, $configB);
        return new Zend_Config($newConfig);
    }

    /**
     * Destruct
     *
     * @return void
     */
    public function __destruct()
    {
        // Detach from events
        $dispatcher = $this->getApp()->getMessageDispatcher();
        $dispatcher->detach($this);

        // Free circular reference
        unset($this->_app);
    }
}