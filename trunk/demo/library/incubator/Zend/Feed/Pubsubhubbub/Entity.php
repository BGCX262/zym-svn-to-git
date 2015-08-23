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
 * @package    Zend_Feed_Pubsubhubbub
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/** @see Zend_Db_Table */
require_once 'Zend/Db/Table.php';

/** 
 * @see Zend_Registry
 * Seems to fix the file not being included by Zend_Db_Table...
 */
require_once 'Zend/Registry.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Pubsubhubbub
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Pubsubhubbub_Entity
{
    /**
     * Zend_Db_Table instance to host database methods
     *
     * @var Zend_Db_Table
     */
    protected $_db = null;
 
    /**
     * Constructor
     * 
     * @param  array $data 
     * @param  Zend_Db_Table_Abstract $tableGateway 
     * @return void
     */
    public function __construct(array $data = null, Zend_Db_Table_Abstract $tableGateway = null)
    {
        if (!is_null($data)) {
            $this->setData($data);
        }
        if (is_null($tableGateway)) {
            $parts = explode('_', get_class($this));
            $table = strtolower(array_pop($parts));
            $this->_db = new Zend_Db_Table($table);
        } else {
            $this->_db = $tableGateway;
        }
    }
 
    /**
     * Cast entity to array
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->_data;
    }
    
    /**
     * Set entity state from array of data
     * 
     * @param  array $data 
     * @return Zend_Feed_Pubsubhubbub_Entity
     */
    public function setData(array $data)
    {
        foreach ($data as $name => $value) {
            $this->{$name} = $value;
        }
        return $this;
    }
 
    /**
     * Overload to entity data
     *
     * Does not allow setting new keys
     * 
     * @param  string $name 
     * @param  mixed $value 
     * @return void
     */
    public function __set($name, $value)
    {
        if (!array_key_exists($name, $this->_data)) {
            throw new Exception('You cannot set new properties'
                . ' on this object');
        }
        $this->_data[$name] = $value;
    }
 
    /**
     * Overload to entity data
     * 
     * @param  string $name 
     * @return mixed
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        }
    }
 
    /**
     * Overload: determine if key exists in data
     * 
     * @param  string $name 
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }
 
    /**
     * Overload: remove a key from the entity data
     * 
     * @param  string $name 
     * @return void
     */
    public function __unset($name)
    {
        if (isset($this->_data[$name])) {
            unset($this->_data[$name]);
        }
    }
}
