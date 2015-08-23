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
class Zend_Feed_Pubsubhubbub_Entity_Subscription
    extends Zend_Feed_Pubsubhubbub_Entity
{
    /**
     * Array of data represented by Entity
     *
     * @var array
     */
    protected $_data = array(
        'id'                 => null,
        'callback'           => null,
        'callback_hash'      => null,
        'topic'              => null,
        'topic_hash'         => null,
        'created_time'       => null,
        'last_modified'      => null,
        'lease_seconds'      => null,
        'expiration_time'    => null,
        'eta'                => null,
        'confirm_failures'   => null,
        'verify_token'       => null,
        'secret'             => null,
        'hmac_algorithm'     => null,
        'subscription_state' => null,
    );
    
    /**
     * Save entity to RDMBS
     * 
     * @return bool
     */
    public function save()
    {
        if (!$this->id) {
            $this->id = md5($this->callback . $this->topic);
        }
        $result = $this->_db->find($this->id);
        if ($result) {
            $this->created_time = $result->current()->created_time;
            $this->_db->update(
                $this->toArray(),
                $this->_db->getAdapter()->quoteInto('id = ?', $key)
            );
            return false;
        }

        $this->_db->insert($this->toArray());
        return true;
    }
    
    /**
     * Save request to queue
     * 
     * @return bool
     */
    public function saveSaveRequest()
    {
        if (!$this->id) {
            $this->id = md5($this->callback . $this->topic);
        }
        $result = $this->_db->find($this->id);
        if (!$result) {
            $this->queueTask('save');
            return true;
        }
        return false;
    }
    
    /**
     * Delete entity from queue
     * 
     * @return bool
     */
    public function delete()
    {
        if (!$this->id) {
            $this->id = md5($this->callback . $this->topic);
        }
        $result = $this->_db->find($this->id);
        if ($result) {
            $this->delete();
            return true;
        }
        return false;
    }
    
    /**
     * Queue a delete request
     * 
     * @return bool
     */
    public function saveDeleteRequest()
    {
        if (!$this->id) {
            $this->id = md5($this->callback . $this->topic);
        }
        $result = $this->_db->find($this->id);
        if ($result) {
            $this->queueTask('delete');
            return true;
        }
        return false;
    }
    
    /**
     * Archive a task
     * 
     * @return bool
     */
    public function archive()
    {
        if (!$this->id) {
            $this->id = md5($this->callback . $this->topic);
        }
        $result = $this->_db->find($this->id);
        if ($result) {
            $this->subscription_state = Zend_Feed_Pubsubhubbub::SUBSCRIPTION_TODELETE;
            $this->save();
            return true;
        }
        return false;
    }
    
    /**
     * Get $count subscribers
     * 
     * @param  string $topicUrl 
     * @param  int $count 
     * @return array
     */
    public function getSubscribers($topicUrl, $count)
    {
        $result = $this->_db->fetchAll(
            $this->_db->select()
                ->where('topic_hash = ?', md5($this->topic))
                ->where('subscription_state = ?', Zend_Feed_Pubsubhubbub::SUBSCRIPTION_VERIFIED)
                ->limit($count, 0)
        );
        $return = array();
        foreach ($results as $result) {
            $return[] = new self($result->toArray(), $this->_db);
        }
        return $return;
    }
    
    /**
     * Enqueue a task
     * 
     * @todo   Unsupported until asynchronous support added to Hub logic
     * @param  string $type 
     * @return void
     */
    public function queueTask($type)
    {
    }
    
    /**
     * @todo   Not supported until asynchronous support added to Hub logic
     * @return void
     */
    public function confirmFailed()
    {
    }
}
