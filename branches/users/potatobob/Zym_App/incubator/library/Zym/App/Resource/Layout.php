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
 * @see Zend_Layout
 */
require_once 'Zend/Layout.php';

/**
 * Init Zend_Layout
 *
 * @author Geoffrey Tran
 * @license http://www.zym-project.com/license New BSD License
 * @category Zym
 * @package Zym_App
 * @subpackage Resource
 * @copyright  Copyright (c) 2008 Zym. (http://www.zym-project.com/)
 */
class Zym_App_Resource_Layout extends Zym_App_Resource_Abstract
{
    /**
     * Resource Dependencies on other resources
     *
     * @var array
     */
    protected $_dependencies = array('View');

    /**
     * Default Config
     *
     * @var array
     */
    protected $_defaultConfig = array(
        Zym_App::ENV_DEFAULT => array(
            'layout_path' => 'layouts',
            'layout'      => 'default',
            'mvc_enabled' => null,
            'content_key' => null,
            'inflector'   => null,
            'view'        => null
        )
    );

    /**
     * Setup autoloader
     *
     * @param Zend_Config $config
     */
    public function setup(Zend_Config $config)
    {
        $configArray = $this->_handleConfig($config);
        $configArray['LayoutPath'] = $this->getApp()->getPath(Zym_App::PATH_APP, $configArray['LayoutPath']);
        Zend_Layout::startMvc($configArray);
    }

    /**
     * Modify config for Zend_Layout
     *
     * @param Zend_Config $config
     * @return array
     */
    protected function _handleConfig(Zend_Config $config)
    {
        // Remove null items
        $inflectedConfig = $config->toArray();
        foreach ($inflectedConfig as $index => $var) {
            if (null === $var) {
                unset($inflectedConfig[$index]);
            }
        }

        // Change underscore items to camelcased
        $inflectedConfig = array_flip($inflectedConfig);
        $inflectedConfig = array_flip(array_map(array($this, '_inflectConfig'), $inflectedConfig));

        return $inflectedConfig;
    }

    /**
     * Private function used by {@see setup()} to convert undercore items to camel
     * case
     *
     * @param string $string
     * @return string
     */
    private function _inflectConfig($string)
    {
        $string = str_replace('_', ' ', $string);
        $string = ucwords($string);
        $string = str_replace(' ', '', $string);
        return $string;
    }
}