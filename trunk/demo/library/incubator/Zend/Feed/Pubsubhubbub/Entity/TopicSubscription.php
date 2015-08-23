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
class Zend_Feed_Pubsubhubbub_Entity_TopicSubscription
    extends Zend_Feed_Pubsubhubbub_Entity
{
    /**
     * Array of data represented by Entity
     *
     * @var array
     */
    protected $_data = array(
        'id'                 => null,
        'hub_url'            => null,
        'hub_url_hash'       => null,
        'topic'              => null,
        'topic_hash'         => null,
        'created_time'       => null,
        'last_modified'      => null,
        'lease_seconds'      => null,
        'expiration_time'    => null,
        'verify_token'       => null,
        'secret'             => null,
        'hmac_algorithm'     => null,
        'subscription_state' => null,
    );
    
    /**
     * Save entity to RDBMS
     * 
     * @return bool
     */
    public function save()
    {
        if (!$this->id) {
            $this->id = $this->_getHashKey();
        }
        $result = $this->_db->find($this->id);
        if ($result) {
            $this->created_time = $result->current()->created_time;
            $this->_db->update(
                $this->toArray(),
                $this->_db->getAdapter()->quoteInto('id = ?', $this->id)
            );
            return false;
        }
        $this->_db->insert($this->toArray());
        return true;
    }
    
    /**
     * Delete entity from RDMBS
     * 
     * @return bool
     */
    public function delete()
    {
        if (!$this->id) {
            $this->id = $this->_getHashKey();
        }
        $result = $this->_db->find($this->id);
        if ($result) {
            $this->_db->delete(
                $this->_db->getAdapter()->quoteInto('id = ?', $this->id)
            );
            return true;
        }
        return false;
    }
    
    /**
     * Get entity hashed key
     * 
     * @return string
     */
    protected function _getHashKey()
    {
        return md5($this->hub_url . $this->topic);
    }
}
