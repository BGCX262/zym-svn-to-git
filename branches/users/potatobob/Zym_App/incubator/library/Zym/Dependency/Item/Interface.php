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
 * CPAN {@link http://search.cpan.org/~adamk/Algorithm-Dependency-1.106/}
 *
 * @author    Geoffrey Tran
 * @license   http://www.zym-project.com/license New BSD License
 * @category  Zym
 * @package   Zym_Dependency
 * @copyright Copyright (c) 2008 Zym. (http://www.zym-project.com/)
 */
interface Zym_Dependency_Item_Interface
{
    /**
     * Get item identifier
     *
     * @return string
     */
    public function getId();

    /**
     * Get dependencies
     *
     * @return array Array of Zym_Dependency_Item_Interface or id's
     */
    public function getDependencies();
}