<?php
/**
 * Zym Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category  Zym
 * @package   Zym_Dependency
 * @copyright Copyright (c) 2008 Zym. (http://www.zym-project.com/)
 * @license   http://www.zym-project.com/license New BSD License
 */

/**
 * @see Zym_Dependency_Item_Interface
 */
require_once 'Zym/Dependency/Item/Interface.php';

/**
 * Implements an item in a dependency heirachy.
 *
 * @author    Geoffrey Tran
 * @license   http://www.zym-project.com/license New BSD License
 * @category  Zym
 * @package   Zym_Dependency
 * @copyright Copyright (c) 2008 Zym. (http://www.zym-project.com/)
 */
class Zym_Dependency_Item implements Zym_Dependency_Item_Interface
{
    /**
     * Item identifier
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
     * Construct
     *
     * @param string $id
     * @param array $depends
     */
    public function __construct($id, array $depends = array())
    {
        $this->setId($id);
        $this->setDependencies($depends);
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
}