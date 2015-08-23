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
 * @see Zym_Dependency
 */
require_once 'Zym/Dependency.php';

/**
 * Implements an ordered dependency heirachy
 *
 * Determines dependencies of an item that must be acted upon before the
 * item itself can be acted upon.
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
class Zym_Dependency_Ordered extends Zym_Dependency
{
    /**
     * Returns the dependencies sorted in correct order.
     *
     * @param array $items
     * @return array
     */
    public function schedule($items = array())
    {
        $schedule    = parent::schedule($items);
        $errorMarker = null;
        $return      = array();
        $selected    = $this->getSelected();
        $itemsIds    = array_keys($this->getItems());

        // Convert to item object to id
        foreach ($schedule as $key => $item) {
            if ($item instanceof Zym_Dependency_Item_Interface) {
                $schedule[$key] = $item->getId();
            }
        }

        while ($id = array_shift($schedule)) {
            // Have we checked every item in the stack?
            if ($id == $errorMarker) {
                /**
                 * @see Zym_Dependency_Exception
                 */
                require_once 'Zym/Dependency/Exception.php';
                throw new Zym_Dependency_Exception(sprintf('Item "%s" does not exist', $id));
            }

            // Are there any un-met dependencies?
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

            $item    = $this->getItem($id);
            $missing = array_diff($item->getDependencies(), $selected);

            if ($this->getIgnoreOrphans()) {
                $missing = array_intersect($itemsIds, $missing);
            }

            if (count($missing)) {
                // Look for circular dependency
                if (in_array($errorMarker, $missing) && isset($schedule[0]) && $schedule[0] == $errorMarker) {
                    /**
                     * @see Zym_Dependency_Exception
                     */
                    require_once 'Zym/Dependency/Exception.php';
                    throw new Zym_Dependency_Exception(sprintf(
                        'Encountered circular dependency in items "%s" and "%s"', $id, $schedule[0]));
                }

                $errorMarker = $id;
                $schedule[]  = $id;
                continue;
            }

            // All dependencies have been met. Add the item to the schedule
            // and to the selected index
            $return[]    = $id;
            $selected[]  = $id;
            $errorMarker = null;
        }

        // Convert id's to item objects
        foreach ($return as $key => $id) {
            if (!$this->hasItem($id)) {
                if ($this->getIgnoreOrphans()) {
                    unset($return[$key]);
                    continue;
                }

                /**
                 * @see Zym_Dependency_Exception
                 */
                require_once 'Zym/Dependency/Exception.php';
                throw new Zym_Dependency_Exception(sprintf('Encountered orphaned item "%s"', $id));
            }

            $return[$key] = $this->getItem($id);
        }

        return $return;
    }
}