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
     * Save subscription to RDMBS
     * 
     * @return bool
     */
    public function setSubscription(array $data)
    {
        if (!isset($data['id'])) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception(
                'ID must be set before attempting a save'
            );
        }
        $result = $this->_db->find($data['id']);
        if ($result) {
            $data['created_time'] = $result->current()->created_time;
            $this->_db->update(
                $data,
                $this->_db->getAdapter()->quoteInto('id = ?', $data['id'])
            );
            return false;
        }

        $this->_db->insert($data);
        return true;
    }
    
    /**
     * Get subscription by ID/key
     * 
     * @param  string $key 
     * @return array
     */
    public function getSubscription($key)
    {
        if (empty($key) || !is_string($key)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid parameter "key"'
                .' of "' . $key . '" must be a non-empty string');
        }
        $result = $this->_db->find($key);
        if ($result) {
            return $result->current()->toArray();
        }
        return false;
    }

    /**
     * Determine if a subscription matching the key exists
     * 
     * @param  string $key 
     * @return bool
     */
    public function hasSubscription($key)
    {
        if (empty($key) || !is_string($key)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid parameter "key"'
                .' of "' . $key . '" must be a non-empty string');
        }
        $result = $this->_db->find($key);
        if ($result) {
            return true;
        }
        return false;
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
