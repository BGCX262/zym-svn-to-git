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
 * @see Zym_Cache
 */
require_once 'Zym/Cache.php';

/**
 * Cache component configuration
 *
 * @author Geoffrey Tran
 * @license http://www.zym-project.com/license New BSD License
 * @category Zym
 * @package Zym_App
 * @subpackage Resource
 * @copyright  Copyright (c) 2008 Zym. (http://www.zym-project.com/)
 */
class Zym_App_Resource_Cache extends Zym_App_Resource_Abstract
{
    /**
     * Default config
     *
     * @var array
     */
    protected $_defaultConfig = array(
        Zym_App::ENV_PRODUCTION => array(
            'frontend' => array(
                'core' => array(
                    'caching' => true
                ),

                'output' => array(
                    'caching' => true
                ),

                'function' => array(
                    'caching' => true
                ),

                'class' => array(
                    'caching' => true
                ),

                'file' => array(
                    'caching' => true
                ),

                'page' => array(
                    'caching' => true
                )
            )
        ),

        Zym_App::ENV_DEFAULT     => array(
            'default_backend' => 'file',

            'frontend' => array(
                'core' => array(
                    'caching' => false,
                    'cache_id_prefix' => '%s__',
                    'automatic_serialization' => true

                ),

                'output' => array(
                    'caching' => false,
                    'cache_id_prefix' => '%s__'
                ),

                'function' => array(
                    'caching' => false,
                    'cache_id_prefix' => '%s__'
                ),

                'class' => array(
                    'caching' => false,
                    'cache_id_prefix' => '%s__'
                ),

                'file' => array(
                    'caching' => false,
                    'cache_id_prefix' => '%s__'
                ),

                'page' => array(
                    'caching' => false,
                    'cache_id_prefix' => '%s__'
                )
            ),

            'backend' => array(
                'file' => array(
                    'cache_dir' => 'cache' // Relative to Zym_App::PATH_DATA
                ),

                'sqlite' => array(
                    'cache_db_complete_path' => 'cache/cache.sqlite' // Relative to Zym_App::PATH_DATA
                )
            )
        )
    );

    /**
     * Setup
     *
     * @param Zend_Config $config
     * @return void
     */
    public function setup(Zend_Config $config)
    {
        // Parse cache id prefix for application name via sprintf
        $config = $this->_parseCacheIdPrefix($config);

        Zym_Cache::setConfig($config);

        // Set file cache dir
        $this->_prependPath($config->get('backend'));
    }

    /**
     * Prepend data path to paths
     *
     * @param Zend_Config $config
     */
    protected function _prependPath(Zend_Config $config)
    {
        $app = $this->getApp();

        // File
        $fileOptions = Zym_Cache::getBackendOptions('file');
        if (isset($fileOptions['cache_dir'])) {
            $fileOptions['cache_dir'] = $app->getPath(Zym_App::PATH_DATA, $fileOptions['cache_dir']);
        }

        Zym_Cache::setBackendOptions('file', $fileOptions);

        // Sqlite
        $sqliteOptions = Zym_Cache::getBackendOptions('sqlite');
        if (isset($sqliteOptions['cache_db_complete_path'])) {
            $sqliteOptions['cache_db_complete_path'] = $app->getPath(Zym_App::PATH_DATA, $sqliteOptions['cache_db_complete_path']);
        }

        Zym_Cache::setBackendOptions('sqlite', $sqliteOptions);
    }

    /**
     * Parse cache frontend option 'cache_id_prefix' to allow
     * sprintf of the application name.
     *
     * %s will be the <name> attrib of Zym_App
     *
     * @param Zend_Config $config
     */
    protected function _parseCacheIdPrefix(Zend_Config $config)
    {
        $name = $this->getApp()->getName(true);
        $configArray = $config->toArray();

        foreach ($configArray['frontend'] as &$frontend) {
        	if (isset($frontend['cache_id_prefix'])) {
        	    $frontend['cache_id_prefix'] = sprintf($frontend['cache_id_prefix'], $name);
        	}
        }
        unset($frontend);

        return new Zend_Config($configArray);
    }
}