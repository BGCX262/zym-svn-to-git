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
 * @subpackage Entity
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** @see Zend_Feed_Pubsubhubbub_Entity */
require_once 'Zend/Feed/Pubsubhubbub/Entity.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Pubsubhubbub
 * @subpackage Entity
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Pubsubhubbub_Entity_FeedRecord
    extends Zend_Feed_Pubsubhubbub_Entity
{
    /**
     * Array of data represented by Entity
     *
     * @var array
     */
    protected $_data = array(
        'topic'         => null,
        'header_footer' => null,
        'last_updated'  => null,
        'content_type'  => null,
        'last_modified' => null,
        'etag'          => null,
    );
}
