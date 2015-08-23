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
 * Base class for implementing various dependency trees
 *
 * A framework for creating simple read-only dependency heirachies,
 * where you have a set of items that rely on other items in the set,
 * and require actions on them as well.
 *
 * This class is a port of Algorithm::Dependency from
 * CPAN {@link http://search.cpan.org/~adamk/Algorithm-Dependency-1.106/} and
 * the another PHP port of which I cannot remember.
 *
 * @author    Geoffrey Tran
 * @license   http://www.zym-project.com/license New BSD License
 * @category  Zym
 * @package   Zym_Dependency
 * @copyright Copyright (c) 2008 Zym. (http://www.zym-project.com/)
 */
class Zym_Dependency
{
    /**
     * Items
     *
     * @var array
     */
    private $_items = array();

    /**
     * Selected dependencies
     *
     * @var array
     */
    private $_selected = array();

    /**
     * Ignore orphans
     *
     * @var boolean
     */
    private $_ignoreOrphans = false;

    /**
     * Construct
     *
     * @param array $items
     */
    public function __construct(array $items = array())
    {
        $this->setItems($items);
    }

    /**
     * Given a list of one or more item names, the depends() method will return
     * an array containing a list of the names of all the OTHER
     * items that also have to be selected to meet dependencies.
     *
     * That is, if item A depends on B and C then the depends() method would
     * return a reference to an array with B and C. ( [ 'B', 'C' ] )
     *
     * If multiple item names are provided, the same applies. The list returned
     * will not contain duplicates.
     *
     * @param array $items The array of items we need to check dependencies for.
     * @return array  The method returns a reference to an array of item names on success, a
     *                reference to an empty array if no other items are needed.
     */
    public function depends(array $items = array())
    {
        // Use only ID's
        foreach ($items as $key => $item) {
            if ($item instanceof Zym_Dependency_Item_Interface) {
                $items[$key] = $item->getId();
            }
        }

        $checked   = array();
        $depends   = array();
        $stack     = $items;

        while ($id = array_shift($stack)) {
            if (!$this->hasItem($id)) {
                if ($this->getIgnoreOrphans()) {
                    continue;
                }

                /**
                 * @see Zym_Dependency_Exception
                 */
                require_once 'Zym/Dependency/Exception.php';
                throw new Zym_Dependency_Exception(sprintf('Encountered orphaned item "%s"', $id));
            }

            $item         = $this->getItem($id);
            $checked[$id] = 1;
            $deps         = $item->getDependencies();
            foreach ($deps as $dependsOnId) {
                if (!isset($checked[$dependsOnId])) {
                    array_push($stack, $dependsOnId);
                }
            }

            if (!in_array($id, $items)) {
                $depends[] = $id;
            }
        }

        // Remove any items already selected
        $depends = array_diff($depends, $this->getSelected());
        sort($depends);

        foreach ($depends as $key => $id) {
            if (!$this->hasItem($id)) {
                if ($this->getIgnoreOrphans()) {
                    continue;
                }

                /**
                 * @see Zym_Dependency_Exception
                 */
                require_once 'Zym/Dependency/Exception.php';
                throw new Zym_Dependency_Exception(sprintf('Encountered orphaned item "%s"', $id));
            }

    	    $depends[$key] = $this->getItem($id);
        }

        return $depends;
    }

    /**
     * Given a list of one or more item names, the depends() method will return,
     * as a reference to an array, the ordered list of items you should act upon.
     *
     * This would be the original names provided, plus those added to satisfy
     * dependencies, in the prefered order of action. For the normal algorithm,
     * where order it not important, this is alphabetical order. This makes it
     * easier for someone watching a program operate on the items to determine
     * how far you are through the task and makes any logs easier to read.
     *
     * If any of the names you provided in the arguments is already selected, it
     * will not be included in the list.
     *
     * @param array $items  The array of items we need to check dependencies for.
     * @return array  The method returns an array of item names on success,
     *                an empty array if no items need to be acted upon, or false
     *                on error.
     */
    public function schedule($items = array())
    {
        // Convert to item object to id
        $itemIds = array();
        foreach ($items as $key => $item) {
            if ($item instanceof Zym_Dependency_Item_Interface) {
                $itemIds[$key] = $item->getId();
            } else {
                $itemIds[$key] = $item;
            }
        }

        // Find dependencies
        $depends = $this->depends($items);

        // Get id's from dependencies
        $dependsIds = array();
        foreach ($depends as $key => $item) {
            $dependsIds[$key] = $item->getId();
        }

        // Return combined list
        $combined = array_merge($itemIds, $dependsIds);
        array_unique($combined);

        // Remove any items already selected
        $combined = array_diff($combined, $this->getSelected());
        sort($combined);

        // Convert id's to item objects
        foreach ($combined as $key => $id) {
            if (!$this->hasItem($id)) {
                if ($this->getIgnoreOrphans()) {
                    unset($combined[$key]);
                    continue;
                }

                /**
                 * @see Zym_Dependency_Exception
                 */
                require_once 'Zym/Dependency/Exception.php';
                throw new Zym_Dependency_Exception(sprintf('Encountered orphaned item "%s"', $id));
            }

            $combined[$key] = $this->getItem($id);
        }

        return $combined;
    }


    /**
     * Schedule all items
     *
     * @return array
     */
    public function scheduleAll()
    {
        return $this->schedule(array_keys($this->getItems()));
    }

    /**
     * Set items
     *
     * @param array $items
     * @return Zym_Dependency
     */
    public function setItems(array $items)
    {
        $assocItems = array();
        foreach ($items as $key => $item) {
            $itemId              = is_int($key) ? $item->getId() : $key;
        	$assocItems[$itemId] = $item;
        }

        $this->_items = $assocItems;
        return $this;
    }

    /**
     * Get items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * Get item by id
     *
     * @param string $id
     * @return Zym_Dependency_Item_Interface
     */
    public function getItem($id)
    {
        if (!isset($this->_items[$id])) {
            /**
             * @see Zym_Dependency_Exception
             */
            require_once 'Zym/Dependency/Exception.php';
            throw new Zym_Dependency_Exception(sprintf('Item "%s" does not exist', $id));
        }

        return $this->_items[$id];
    }

    /**
     * Check if item exists
     *
     * @return boolean
     */
    public function hasItem($id)
    {
        return isset($this->_items[$id]);
    }

    /**
     * Set selected dependencies
     *
     * @param array $selected
     * @return Zym_Dependency
     */
    public function setSelected(array $selected)
    {

        $this->_selected = $selected;
        return $this;
    }

    /**
     * Get selected dependencies
     *
     * @return array
     */
    public function getSelected()
    {
        return $this->_selected;
    }

    /**
     * Set ignore orphans flag
     *
     * @param boolean $flag
     * @return Zym_Dependency
     */
    public function setIgnoreOrphans($flag)
    {
        $this->_ignoreOrphans = $flag;
        return $this;
    }

    /**
     * Get ignore orphans flag
     *
     * @return boolean
     */
    public function getIgnoreOrphans()
    {
        return $this->_ignoreOrphans;
    }
}