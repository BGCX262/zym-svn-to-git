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
 * @see Zend_Filter_TwoWay_TwoWayInterface
 */
require_once 'Zend/Filter/TwoWay/TwoWayInterface.php';

/**
 * @see Zend_Loader
 */
require_once 'Zend/Loader.php';

/**
 * Generic TwoWay filter, allows to use two way direction filtering
 *
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_TwoWay implements Zend_Filter_TwoWay_TwoWayInterface, Zend_Filter_Interface
{
    /**
     * TwoWay adapter
     */
    protected $_adapter;

    /**
     * Default adapter
     */
    protected static $_defaultAdapter;

    /**
     * Default namespace
     */
    protected static $_defaultNamespace = 'Zend_Filter';

    /**
     * Class constructor
     *
     * @param string|array $options (Optional) Options to set
     */
    public function __construct($options = null)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        $this->setAdapter($options);
    }

    /**
     * Returns the actual set default adapter
     *
     * @return $string The actual set default adapter
     */
    public static function getDefaultAdapter()
    {
        return self::$_defaultAdapter;
    }

    /**
     * Sets a new default adapter to use
     *
     * @param string $adapter
     */
    protected static function _setDefaultAdapter($adapter)
    {
        $path = str_replace('_', DIRECTORY_SEPARATOR, self::$_defaultNamespace) . DIRECTORY_SEPARATOR;
        if (Zend_Loader::isReadable($path . ucfirst($adapter). '.php')) {
            $adapter = self::$_defaultNamespace . '_' . ucfirst($adapter);
        }

        self::$_defaultAdapter = $adapter;
        return true;
    }

    /**
     * Returns the default namespace for this filter type
     *
     * @return string
     */
    protected static function _getDefaultNamespace()
    {
        return self::$_defaultNamespace;
    }

    /**
     * Sets the default namespace for this filter type
     *
     * @param string $path Default Namespace for this filter type
     * @return string
     */
    protected static function _setDefaultNamespace($path)
    {
        self::$_defaultNamespace = $path;
        return self::$_defaultNamespace;
    }

    /**
     * Returns the adapter
     *
     * @return Object
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * Sets a new adapter
     *
     * @param  string|array $options (Optional) Adapter with Options
     * @return Zend_Filter_TwoWay
     */
    public function setAdapter($options = null)
    {
        if (is_string($options)) {
            $adapter = $options;
        } else if (isset($options['adapter'])) {
            $adapter = $options['adapter'];
            unset($options['adapter']);
        } else if (!empty(self::$_defaultAdapter)) {
            $adapter = self::getDefaultAdapter();
        } else {
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception("This filter needs the option 'adapter'");
        }

        if (!is_array($options)) {
            $options = array();
        }

        $path = str_replace('_', DIRECTORY_SEPARATOR, self::$_defaultNamespace) . DIRECTORY_SEPARATOR;
        if (Zend_Loader::isReadable($path . ucfirst($adapter). '.php')) {
            $adapter = self::$_defaultNamespace . '_' . ucfirst($adapter);
        }

        if (!class_exists($adapter)) {
            Zend_Loader::loadClass($adapter);
        }

        $this->_adapter = new $adapter($options);
        if (!$this->_adapter instanceof Zend_Filter_TwoWay_TwoWayInterface) {
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception("Adapter '" . $adapter . "' does not implement Zend_Filter_TwoWay_TwoWayInterface");
        }

        foreach ($options as $key => $option) {
            if (method_exists($this->_adapter, 'set' . ucfirst($key))) {
                call_user_func_array(array($this->_adapter, 'set' . ucfirst($key)), $option);
                unset($options[$key]);
            }
        }

        return $this;
    }

    /**
     * Calls adapter methods
     *
     * @param string       $method  Method to call
     * @param string|array $options Options for this method
     */
    public function __call($method, $options)
    {
        if (!method_exists($this->_adapter, $method)) {
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception("Unknown method '{$method}'");
        }

        return call_user_func_array(array($this->_adapter, $method), $options);
    }

    /**
     * Defined by Zend_Filter_TwoWay_Interface
     *
     * Filters the original $value TO the filtered value
     *
     * @param  string $value Filters from original TO filtered
     * @return string The filtered content
     */
    public function filterTo($value)
    {
        return $this->_adapter->filterTo($value);
    }

    /**
     * Defined by Zend_Filter_TwoWay_Interface
     *
     * Returns the original value FROM the filtered $value
     *
     * @param  string $value Filters from filtered to original
     * @return string The original content
     */
    public function filterFrom($value)
    {
        return $this->_adapter->filterFrom($value);
    }

    /**
     * Defined by Zend_Filter_TwoWay_Interface
     *
     * Returns the original value FROM the filtered $value
     *
     * @param  string $value Filters from filtered to original
     * @param  string $direction Direction, FALSE = TO, TRUE = FROM
     * @return string The original content
     */
    public function filter($value, $direction = false)
    {
        if ($direction == false) {
            return $this->filterTo($value);
        }

        return $this->filterFrom($value);
    }

    public function toString()
    {
        return $this->_adapter->toString();
    }
}
