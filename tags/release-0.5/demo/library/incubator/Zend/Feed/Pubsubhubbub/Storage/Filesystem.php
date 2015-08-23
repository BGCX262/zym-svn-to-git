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
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Feed_Pubsubhubbub_Storage_StorageInterface
 */
require_once 'Zend/Feed/Pubsubhubbub/Storage/StorageInterface.php';

/**
 * @see Zend_Uri
 */
require_once 'Zend/Uri.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Pubsubhubbub
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Pubsubhubbub_Storage_Filesystem 
    implements Zend_Feed_Pubsubhubbub_Storage_StorageInterface
{
    /**
     * The directory to which values will be stored. If left unset, will attempt
     * to detect and use a valid writable temporary directory.
     *
     * @var string
     */
    protected $_directory = null;

    /**
     * Set the directory to which values will be stored.
     *
     * @param  string $directory
     * @return Zend_Feed_Pubsubhubbub_Storage_Filesystem
     */
    public function setDirectory($directory)
    {
        if (!file_exists($directory) || !is_writable($directory)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('The directory "'
                . $directory . '" is not writable or does not exist and therefore'
                . ' cannot be used');
        }
        $this->_directory = rtrim($directory, '/\\');
        foreach (array('subscription','known_feed') as $subdir) {
            if (!file_exists($directory . '/' . $subdir)) {
                mkdir($directory . '/' . $subdir);
            }
        }
        return $this;
    }

    /**
     * Get the directory to which values will be stored.
     *
     * @return string
     */
    public function getDirectory($subdir = null)
    {
        if ($this->_directory === null) {
            $this->setDirectory(sys_get_temp_dir());
        }
        if ($subdir) {
            return $this->_directory . '/' . $subdir;
        }
        return $this->_directory;
    }

    /**
     * Set subscription data
     * 
     * @param  string $key 
     * @param  array $data 
     * @return Zend_Feed_Pubsubhubbub_Storage_Filesystem
     */
    public function setSubscription($key, array $data)
    {
        if (empty($key) || !is_string($key)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid parameter "key"'
                . ' of "' . $key . '" must be a non-empty string');
        }
        $filename = $this->_getFilename($key);
        $path     = $this->getDirectory('subscription') . '/' . $filename;
        file_put_contents($path, serialize($data));
        return $this;
    }

    /**
     * Get subscription by key
     * 
     * @param  string $key 
     * @return array
     */
    public function getSubscription($key)
    {
        if (empty($key) || !is_string($key)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid parameter "data"'
                .' of "' . $data . '" must be a non-empty string');
        }
        $filename = $this->_getFilename($key);
        $path     = $this->getDirectory('subscription') . '/' . $filename;
        if (!file_exists($path) || !is_readable($path)) {
            return false;
        }
        $serialized = file_get_contents($path);
        if (empty($serialized)) {
            return false;
        }
        $data = unserialize($serialized);
        return $data;
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
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid parameter "data"'
                . ' of "' . $data . '" must be a non-empty string');
        }
        $filename = $this->_getFilename($key);
        $path     = $this->getDirectory('subscription') . '/' . $filename;
        if (!file_exists($path) || !is_readable($path)) {
            return false;
        }
        return true;
    }

    /**
     * Remove a subscription
     * 
     * @param  string $key 
     * @return Zend_Feed_Pubsubhubbub_Storage_Filesystem
     */
    public function removeSubscription($key)
    {
        if (empty($key) || !is_string($key)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid parameter "data"'
                . ' of "' . $data . '" must be a non-empty string');
        }
        $filename = $this->_getFilename($key);
        $path     = $this->getDirectory('subscription') . '/' . $filename;
        if (!file_exists($path) || !is_readable($path)) {
            return $this;
        }
        unlink($path);
        return $this;
    }
    
    /**
     * Set a known feed location and related data
     * 
     * @param  mixed $key 
     * @param  array $data 
     * @return Zend_Feed_Pubsubhubbub_Storage_Filesystem
     */
    public function setKnownFeed($key, array $data)
    {
        if (empty($key) || !is_string($key)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid parameter "key"'
                . ' of "' . $key . '" must be a non-empty string');
        }
        $filename = $this->_getFilename($key);
        $path     = $this->getDirectory('known_feed') . '/' . $filename;
        file_put_contents($path, serialize($data));
        return $this;
    }

    /**
     * Pull information about a known feed location
     * 
     * @param  string $key 
     * @return false|array False if no related location found or no data for that location
     */
    public function getKnownFeed($key)
    {
        if (empty($key) || !is_string($key)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid parameter "data"'
                . ' of "' . $data . '" must be a non-empty string');
        }
        $filename = $this->_getFilename($key);
        $path     = $this->getDirectory('known_feed') . '/' . $filename;
        if (!file_exists($path) || !is_readable($path)) {
            return false;
        }
        $serialized = file_get_contents($path);
        if (empty($serialized)) {
            return false;
        }
        $data = unserialize($serialized);
        return $data;
    }

    /**
     * Determine if we know about a given feed location
     * 
     * @param  string $key 
     * @return bool
     */
    public function hasKnownFeed($key)
    {
        if (empty($key) || !is_string($key)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid parameter "data"'
                . ' of "' . $data . '" must be a non-empty string');
        }
        $filename = $this->_getFilename($key);
        $path     = $this->getDirectory('known_feed') . '/' . $filename;
        if (!file_exists($path) || !is_readable($path)) {
            return false;
        }
        return true;
    }

    /**
     * Remove all information about a known feed
     * 
     * @param  string $key 
     * @return Zend_Feed_Pubsubhubbub_Storage_Filesystem
     */
    public function removeKnownFeed($key)
    {
        if (empty($key) || !is_string($key)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid parameter "data"'
                . ' of "' . $data . '" must be a non-empty string');
        }
        $filename = $this->_getFilename($key);
        $path = $this->getDirectory('known_feed') . '/' . $filename;
        if (!file_exists($path) || !is_readable($path)) {
            return $this;
        }
        unlink($path);
        return $this;
    }

    /**
     * When/If implemented: deletes all records for any given valid Type
     *
     * @param  string $type
     * @return void
     * @throws Zend_Feed_Pubsubhubbub_Exception
     */
    public function cleanup($type)
    {
        require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
        throw new Zend_Feed_Pubsubhubbub_Exception('Not Implemented');
    }

    /**
     * Based on parameters, generate a valid one-way hashed filename for a
     * store entry
     *
     * @param  string $key Location key
     * @return string
     */
    protected function _getFilename($key)
    {
        return preg_replace(
            array("/\+/", "/\//", "/\=/"),
            array('_', '.', ''), 
            base64_encode(sha1($key))
        );
    }
}
