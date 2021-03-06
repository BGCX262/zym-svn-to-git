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
 * @package Zym_View
 * @subpackage Filter
 * @copyright  Copyright (c) 2008 Zym. (http://www.zym-project.com/)
 * @license http://www.zym-project.com/license New BSD License
 */

/**
 * Abstract view filter
 *
 * @author Geoffrey Tran
 * @license http://www.zym-project.com/license New BSD License
 * @package Zym_View
 * @subpackage Filter
 * @copyright  Copyright (c) 2008 Zym. (http://www.zym-project.com/)
 */
interface Zym_View_Filter_Interface
{
    /**
     * Filter
     *
     * @param  string $buffer
     * @return string
     */
    public function filter($buffer);
}