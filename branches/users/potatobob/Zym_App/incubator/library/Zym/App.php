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
 * @copyright Copyright (c) 2008 Zym. (http://www.zym-project.com/)
 * @license http://www.zym-project.com/license New BSD License
 */

/**
 * @see Zend_Cache
 */
require_once 'Zend/Cache.php';

/**
 * @see Zend_Config
 */
require_once 'Zend/Config.php';

/**
 * @see Zend_Loader
 */
require_once 'Zend/Loader.php';

/**
 * @see Zend_Loader_PluginLoader
 */
require_once 'Zend/Loader/PluginLoader.php';

/**
 * @see Zym_Message_Dispatcher
 */
require_once 'Zym/Message/Dispatcher.php';

/**
 * Bootstraping component
 *
 * @author Geoffrey Tran
 * @license http://www.zym-project.com/license New BSD License
 * @category Zym
 * @package Zym_App
 * @copyright Copyright (c) 2008 Zym. (http://www.zym-project.com/)
 */
class Zym_App
{
    /**
     * Environment type development
     *
     */
    const ENV_DEVELOPMENT = 'development';

    /**
     * Environment type production
     *
     */
    const ENV_PRODUCTION  = 'production';

    /**
     * Environment type test
     *
     */
    const ENV_TEST        = 'test';

    /**
     * Environment type cli
     *
     */
    const ENV_CLI         = 'cli';

    /**
     * Environment type default
     */
    const ENV_DEFAULT     = 'default';

    /**
     * Config directory
     *
     */
    const PATH_CONFIG = 'config';

    /**
     * Temp directory
     *
     */
    const PATH_TEMP   = 'temp';

    /**
     * Web directory
     *
     */
    const PATH_WEB    = 'web';

    /**
     * Application directory
     *
     */
    const PATH_APP    = 'app';

    /**
     * Data directory
     *
     */
    const PATH_DATA   = 'data';

    /**
     * Tests directory
     *
     */
    const PATH_TESTS  = 'tests';

    /**
     * Instance
     *
     * @var Zym_App
     */
    protected static $_instance;

    /**
     * Cache object
     *
     * @var Zend_Cache_Core
     */
    protected $_cache;

    /**
     * Zend_Config
     *
     * @var Zend_Config
     */
    protected $_config;

    /**
     * Default config
     *
     * @var array
     */
    protected $_defaultConfig = array(
        self::ENV_PRODUCTION  => array(
            'cache' => array(
                'enabled' => true
            )
        ),

        self::ENV_DEVELOPMENT => array(),

        self::ENV_TEST        => array(),

        self::ENV_CLI         => array(
            'home' => '../../' // From batch/jobs
        ),

        self::ENV_DEFAULT     => array(
            'name' => 'App',

            'home' => '../',

            'namespace' => array(
                'Zym' => 'Zym_App_Resource'
            ),

            'path' => array(
                self::PATH_APP    => 'app',
                self::PATH_CONFIG => 'config',
                self::PATH_DATA   => 'data',
                self::PATH_TEMP   => 'temp',
                self::PATH_TESTS  => 'tests',
                self::PATH_WEB    => 'web',
            ),

            'default_resource' => array(
                'disabled'    => false,
                'config'      => '%s.xml',
                'environment' => null,
                'priority'    => null
            ),

            'cache' => array(
                'enabled' => false,
                'backend' => null
            ),

            'resource' => array()
        )
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
    protected $_environment = self::ENV_PRODUCTION;

    /**
     * Array of resource instances
     *
     * @var array
     */
    protected $_resources = array();

    /**
     * Resource loader
     *
     * @var Zend_Loader_PluginLoader
     */
    protected $_loader;

    /**
     * Message Dispatcher
     *
     * @var Zym_Message_Dispatcher
     */
    protected $_messageDispatcher;

    /**
     * Construct
     *
     * Protected to prevent instantiation
     *
     * @return void
     */
    protected function __construct()
    {
        $dispatcher = $this->getMessageDispatcher();
        $dispatcher->attach($this, 'afterResourceSetup', 'onAfterResourceSetup');
    }

    /**
     * Clone
     *
     * Enforce singleton
     *
     * @return void
     */
    protected function __clone()
    {}

    /**
     * Get the application instance
     *
     * @return Zym_App
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Config
     *
     * @param Zend_Config|string
     * @return Zym_App
     */
    public function setConfig($config, $format = null)
    {
        $environment = $this->getEnvironment();

        if (is_string($config)) {
            $configObj = $this->_loadConfig($config, $environment, $format);
        } else {
            $configObj = $config;
        }

        // Merge default config with user config
        $defaultConfig = $this->getDefaultConfig($environment);
        $this->_config = $this->_mergeConfig($defaultConfig, $configObj);

        return $this;
    }

    /**
     * Get Config
     *
     * Retrieves the config of the current environment
     *
     * @return Zend_Config
     */
    public function getConfig()
    {
        if (!$this->_config instanceof Zend_Config) {
            $this->setConfig(array());
        }

        return $this->_config;
    }

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
            if (isset($this->_defaultConfig[self::ENV_DEFAULT])) {
                $config = $this->_arrayMergeRecursiveOverwrite($this->_defaultConfig[self::ENV_DEFAULT], $config);
            }

            $this->_defaultConfigObject = new Zend_Config($config);
        }

        return $this->_defaultConfigObject;
    }

    /**
     * Get path
     *
     * @param string $key
     * @param string $append
     * @return Zend_Config
     */
    public function getPath($key, $append = null)
    {
        // Return root instead
        $relativeRoot = substr($append, 0, 2);
        if (in_array($relativeRoot{0}, array('/', '\\')) || $relativeRoot{1} == ':') {
            return $append;
        }

        $config = $this->getConfig();

        if (isset($config->path->{$key})) {
            $path = $this->getHome($this->_normalizePath($config->get('path')->get($key)));
        } else {
            /**
             * @see Zym_App_Exception
             */
            require_once 'Zym/App/Exception.php';
            throw new Zym_App_Exception(sprintf('Path "%s" does not exist.', $key));
        }

        // Append for relative paths
        if (!empty($append)) {
            $path .= $append;
        }

        return $path;
    }

    /**
     * Get home directory
     *
     * The home directory is the current working directory for this bootstrap
     * class. By referencing from the home, it allows this component to be
     * CLI friendly.
     *
     * Providing append allows it to append to the home path another path.
     * If the appending path is absolute, it will return the path instead.
     *
     * @param string $append
     * @return string
     */
    public function getHome($append = null)
    {
        // Return root instead
        $relativeRoot = substr((string) $append, 0, 2);
        if (in_array($relativeRoot{0}, array('/', '\\')) || $relativeRoot{1} == ':') {
            return $append;
        }

        $config = $this->getConfig();

        if (!isset($config->home)) {
            /**
             * @see Zym_App_Exception
             */
            require_once 'Zym/App/Exception.php';
            throw new Zym_App_Exception('Config key "home" is not set');
        }

        $home = $this->_normalizePath($config->get('home'));

        // Append home for relative paths
        if (!empty($append)) {
            $home .= $append;
        }

        return $home;
    }

    /**
     * Get name
     *
     * @param boolean $asId
     * @return string
     */
    public function getName($asId = false)
    {
        $name = $this->getConfig()->get('name');

        if ($asId === true) {
            $name = str_replace(' ', '_', $name);

            // Allow only Alnum
            $pattern = '/[^a-zA-Z0-9_]/';
            $name = preg_replace($pattern, '', (string) $name);
        }

        return (string) $name;
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
        $cache->setOption('cache_id_prefix', $prefix .  $this->getEnvironment() . '__' . get_class($this) .'__');

        $this->_cache = $cache;

        return $this;
    }

    /**
     * Get cache object
     *
     * @param string $id
     * @return Zend_Cache_Core|mixed
     */
    public function getCache($id = null)
    {
        // Sanity check
        if (!$this->_cache instanceof Zend_Cache_Core) {
            /**
             * @see Zym_App_Exception
             */
            require_once('Zym/App/Exception.php');
            throw new Zym_App_Exception('Cache object has not been set.');
        }

        if ($id !== null) {
            return $this->_cache->load($id);
        }

        return $this->_cache;
    }

    /**
     * Add a script path to the stack
     *
     * @param string $path
     * @param string $prefix
     * @return Zym_App
     */
    public function addResourcePath($path, $prefix = 'Zym_App_Resource')
    {
        $this->getPluginLoader()->addPrefixPath($prefix, $path);

        return $this;
    }

    /**
     * Add repository of init scripts by prefix
     *
     * @param string $prefix
     * @return Zym_App
     */
    public function addResourcePrefix($prefix)
    {
        $path = str_replace('_', DIRECTORY_SEPARATOR, $prefix);
        $this->addResourcePath($path, $prefix);

        return $this;
    }

    /**
     * Append a resource script into the dispatch process
     *
     * @param Zym_App_Resource_Abstract $resource
     * @return Zym_App
     */
    public function appendResource(Zym_App_Resource_Abstract $resource, $name = null)
    {
        // Get the resource name (Zym_Foo -> Foo)
        if ($name === null) {
            $fullClassName = get_class($resource);

            if (strpos($fullClassName, '_') !== false) {
                $name = strrchr($fullClassName, '_');
                $name = ltrim($name, '_');
            } else {
                $name = $fullClassName;
            }
        }

        $this->_resources[$name] = $resource;

        return $this;
    }

    /**
     * Returns resource with the specified $name
     *
     * @param string $name  resource name
     * @return Zym_App_Resource_Abstract
     * @throws Zym_App_Resource_Exception  if $name is invalid
     */
    public function getResource($name)
    {
        if (!is_string($name) || empty($name)) {
            /**
             * @see Zym_App_Resource_Exception
             */
            require_once 'Zym/App/Resource/Exception.php';
            throw new Zym_App_Resource_Exception('Invalid resource name');
        }

        // first, check if the name is explictly given to a resource
        if (array_key_exists($name, $this->_resources)) {
            return $this->_resources[$this->_normalizeName($name)];
        }

        $name = strtolower($name);

        // loop resources
        foreach ($this->_resources as $resource) {
            $className = strtolower(get_class($resource));

            // if $name contains an underscore, assume full class name is given
            if (strpos($name, '_') !== false && $name == $className) {
                return $resource;
            }

            // extract resource name
            $className = split('_', $className);
            if ($className[count($className) - 1] == $name) {
                return $resource;
            }
        }

        /**
         * @see Zym_App_Resource_Exception
         */
        require_once 'Zym/App/Resource/Exception.php';
        throw new Zym_App_Resource_Exception(sprintf('Invalid resource name "%s"', $name));
    }

    /**
     * Clear resource scripts
     *
     * @return Zym_App
     */
    public function clearResources()
    {
        $this->setResources(array());
        return $this;
    }

    /**
     * Clear and set init scripts
     *
     * @param array $scripts Array of Zym_App_Resource_Abstract
     * @return Zym_App
     */
    public function setResources(array $scripts)
    {
        foreach ($scripts as $script) {
            if (!$script instanceof Zym_App_Resource_Abstract) {
                /**
                 * @see Zym_App_Exception
                 */
                require_once('Zym/App/Exception.php');
                throw new Zym_App_Exception(sprintf(
                    'The array of resource scripts provided has an invalid entry "%s".'
                    . 'It should consist only of Zym_App_Resource_Abstract instances',
                    get_class($script)
                ));
            }
        }

        $this->_resources = $scripts;
        return $this;
    }

    /**
     * Get array of init script instances
     *
     * @return array
     */
    public function getResources()
    {
        return $this->_resources;
    }

    /**
     * Get script paths
     *
     * @return array
     */
    public function getResourcePaths()
    {
        return $this->getPluginLoader()->getPaths();
    }

    /**
     * Set environment
     *
     * @param string $environment
     * @return Zym_App
     */
    public function setEnvironment($environment)
    {
        $this->_environment = (string) $environment;
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
     * Set plugin loader
     *
     * @param  Zend_Loader_PluginLoader $loader
     * @return Zym_App
     */
    public function setPluginLoader(Zend_Loader_PluginLoader $loader)
    {
        $this->_loader = $loader;
        return $this;
    }

    /**
     * Get plugin loader
     *
     * @return Zend_Loader_PluginLoader
     */
    public function getPluginLoader()
    {
        if (!$this->_loader instanceof Zend_Loader_PluginLoader) {
            $loader = new Zend_Loader_PluginLoader(array(
                'Zym_App_Resource_' => 'Zym/App/Resource/'
            ));

            $this->_loader = $loader;
        }

        return $this->_loader;
    }

    /**
     * Set Message Dispatcher
     *
     * @param Zym_Message_Dispatcher $dispatcher
     * @return Zym_App
     */
    public function setMessageDispatcher(Zym_Message_Dispatcher $dispatcher)
    {
        $this->_messageDispatcher = $dispatcher;

        return $this;
    }

    /**
     * Get Message Dispatcher
     *
     * @return Zym_Message_Dispatcher
     */
    public function getMessageDispatcher()
    {
        if (!$this->_messageDispatcher instanceof Zym_Message_Dispatcher) {
            $className  = get_class($this);
            $dispatcher = Zym_Message_Dispatcher::get($className);

            $this->_messageDispatcher = $dispatcher;
        }

        return $this->_messageDispatcher;
    }

    /**
     * Dispatch the boot process
     *
     * @return void
     */
    public function dispatch()
    {
        $dispatcher = $this->getMessageDispatcher();
        $dispatcher->post('beforeDispatch', $this);

        // Get config
        $config = $this->getConfig();

        // Cache setup
        $this->_setupCache($config);

        // Load namespaces
        $this->_parseNamespaces($config);

        // Load resources
        $dispatcher->post('beforeLoadResources', $this);
        $this->_parseResources($config);
        $dispatcher->post('afterLoadResources', $this);

        $this->_sortDispatchDependencies();

        // Init script dispatch loop
        foreach ($this->getResources() as $resource) {
        	$resource->dispatch();
        }


        $dispatcher->post('afterDispatch', $this);
    }

    /**
     * Run application
     *
     * @param Zend_Config|string $config
     * @param string             $environment
     * @param string             $format Configuration format ("ini")
     */
    public static function run($config, $environment = null, $format = null)
    {
        $instance = self::getInstance();

        // Set environment
        if ($environment !== null) {
            $instance->setEnvironment($environment);
        }

        $instance->setConfig($config, $format)
                 ->dispatch();
    }

    /**
     * Parse the config for resources
     *
     * @param Zend_Config $config
     */
    protected function _parseResources(Zend_Config $config)
    {
        $currentResources = $this->getResources();
        $resources        = array();
        $resourcesConfig  = $config->get('resource');

        // No resources to handle
        if (!$resourcesConfig instanceof Zend_Config) {
            return;
        }

        $pluginLoader = $this->getPluginLoader();

        // Lets handle resources provided by config
        foreach ($resourcesConfig as $name => $rawResConfig) {
            if (!($resConfig = $this->getCache("resource_{$name}_config"))
                    || !($environment = $this->getCache("resource_{$name}_environment"))) {
                // Get default resource config
                $defaultResConfig = $config->get('default_resource')->toArray();

                // Convert placeholder to filename
                if (is_string($defaultResConfig['config'])) {
                    $defaultResConfig['config'] = sprintf($defaultResConfig['config'], $name);
                }

                // Merge default config with actual config
                $resource = $this->_mergeConfig($defaultResConfig, $rawResConfig);

                // Run if enabled
                if ($resource->get('disabled') === '' || $resource->get('disabled')) {
                    continue;
                }

                // Environment
                $environment = $resource->get('environment') ? $resource->get('environment') : $this->getEnvironment();

                // Load resource config
                if (!$resource->get('config') instanceof Zend_Config) {
                    // Load a resource config from file specified
                    $resConfigFile = $this->getPath(self::PATH_CONFIG, $resource->get('config'));

                    // Make sure it exists
                    if (file_exists($resConfigFile)) {
                        // Create config obj
                        $resConfig = $this->_loadConfig($resConfigFile, $environment);
                    } else {
                        $resConfig = new Zend_Config(array());
                    }
                } else {
                    // Use the config provided
                    $resConfig = $resource->get('config');
                }

                $this->getCache()->save($resConfig, "resource_{$name}_config");
                $this->getCache()->save($environment, "resource_{$name}_environment");
            }

            // Load resource object
            $loadedScript = $pluginLoader->load($name);
            $script       = new $loadedScript($this, $resConfig, $environment);

            // Make sure that it's a valid script
            if (!$script instanceof Zym_App_Resource_Abstract) {
                /**
                 * @see Zym_App_Exception
                 */
                require_once 'Zym/App/Exception.php';
                throw new Zym_App_Exception(
                    "Resource script \"$name\" is not an instance of Zym_App_Resource_Abstract"
                );
            }

            // Add into dispatch stack
            $resources[$this->_normalizeName($name)] = $script;

            // Cleanup
            unset($resConfig, $environment);
        }

        // Already set resources should override config
        $resources = array_merge($resources, $currentResources);
        $this->setResources($resources);
    }

    /**
     * Sort dispatch resource dependencies
     *
     * @return void
     */
    protected function _sortDispatchDependencies()
    {
        $resources          = $this->getResources();
        $dependencyResolver = new Zym_Dependency_Ordered($resources);
        $dependencyResolver->setIgnoreOrphans(true);

        $ordered = $dependencyResolver->scheduleAll();
        $this->setResources($ordered);
    }

    /**
     * Setup Cache
     *
     * @param Zend_Config $config
     */
    protected function _setupCache(Zend_Config $config)
    {
        // Disable cache
        if (!$config->get('cache')->get('enabled')) {
            $cache = $this->_cache;

            if ($cache instanceof Zend_Cache_Core) {
                $cache = clone $cache->setOption('caching', false);
            } else {
                $cache = Zend_Cache::factory('Core', 'File', array('caching' => false));
            }
        } else {
            $filename = $this->getPath(self::PATH_DATA, 'cache');
            $appCache = Zend_Cache::factory('Core', 'File', array('automatic_serialization' => true,
                                                                  'cache_id_prefix'         => $this->getName(true) . '__' .  $this->getEnvironment() . '__' . get_class($this) . '__'),
                                                            array('cache_dir'        => $filename,
                                                                  'file_name_prefix' => get_class($this)));
            if ($cache = $appCache->load('cache')) {
                $cache = Zend_Cache::factory('Core', $cache[0], $cache[1], $cache[2]);
            } else {
                $cache = Zend_Cache::factory('Core', 'File', array('caching' => false));
            }
        }

        $this->setCache($cache);
    }

    /**
     * Setup cache after resources are setup
     *
     * @internal
     * @param Zym_Message $message
     */
    public function onAfterResourceSetup(Zym_Message $message)
    {
        $sender = $message->getSender();
        if ($sender instanceof Zym_App_Resource_Cache) {
            $config  = $this->getConfig('cache');
            $backend = $config->get('backend')
                        ? $config->get('backend')
                        : Zym_Cache::getDefaultBackend();

            $backendConfig  = Zym_Cache::getBackendOptions($backend);
            $frontendConfig = Zym_Cache::getFrontendOptions('Core');

            // Save
            $filename = $this->getPath(self::PATH_DATA, 'cache');
            $cache = Zend_Cache::factory('Core', 'File', array('automatic_serialization' => true,
                                                               'cache_id_prefix'         => $this->getName(true) . '__' .  $this->getEnvironment() . '__' . get_class($this) .'__'),
                                                         array('cache_dir'        => $filename,
                                                               'file_name_prefix' => get_class($this)));

            $cache->save(array($backend, $frontendConfig, $backendConfig), 'cache');
        }
    }

    /**
     * Load Namespaces
     *
     * @return void
     */
    protected function _parseNamespaces(Zend_Config $config)
    {
        $namespaces = $config->get('namespace');
        foreach ($namespaces as $id => $namespace) {
            if ($namespace instanceof Zend_Config) {
                $this->addResourcePath($namespace->get('path'), $namespace->get('prefix'));
            } else {
                // Allow setting namespaces using keys
                if (empty($namespace)) {
                    $namespace = $id;
                }

                $this->addResourcePrefix($namespace);
            }
        }
    }

    /**
     * Load config file
     *
     * @param string $config
     * @param string $format
     *
     * @return Zend_Config
     *
     * @todo Review hack
     */
    protected function _loadConfig($config, $environment, $format = null)
    {
        // Find format
        if ($format === null) {
            $format = pathinfo($config, PATHINFO_EXTENSION);
        }

        $configClass = 'Zend_Config_' . ucfirst(strtolower($format));
        Zend_Loader::loadClass($configClass);

        try {
            $configObj = new $configClass($config, $environment);
        } catch (Zend_Config_Exception $e) {
            if (!preg_match('/Section \'(?:.+)\' cannot be found in/', $e->getMessage())) {
                throw $e;
            }

            // Try with default section
            try {
                $configObj = new $configClass($config, self::ENV_DEFAULT);
            } catch (Zend_Config_Exception $e) {
                if (!preg_match('/Section \'(?:.+)\' cannot be found in/', $e->getMessage())) {
                    throw $e;
                }

                // Try without sections
                $configObj = new $configClass($config);
            }
        }

        return $configObj;
    }

    /**
     * Normalize a path
     *
     * Trims and removes and leading /\ and adds /
     *
     * @param string $path
     * @return string
     */
    protected function _normalizePath($path)
    {
        return rtrim(trim($path), '/\\') . '/';
    }

    /**
     * Normalize resource name
     *
     * turns foo_bar into Foo_Bar and bar into Bar
     *
     * @param string $name
     * @return string
     */
    protected function _normalizeName($name)
    {
        return implode('_', array_map('ucfirst', explode('_', $name)));
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
        $dispatcher = $this->getMessageDispatcher();
        $dispatcher->detach($this);
    }
}