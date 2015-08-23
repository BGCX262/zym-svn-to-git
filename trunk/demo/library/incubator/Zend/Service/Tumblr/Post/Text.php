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
 * @package    Zend_Service
 * @subpackage Tumblr
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: $
 */

/**
 * @see Zend_Service_Tumblr_Post_Abstract
 **/
require_once 'Zend/Service/Tumblr/Post/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Tumblr
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Tumblr_Post_Text extends Zend_Service_Tumblr_Post_Abstract
{    
    /**
     * Post title
     *
     * @var string|null
     */
    protected $_title;
    
    /**
     * Post body content
     *
     * @var string|null
     */
    protected $_body;
    
    /**
     * Sets text post specific properties
     *
     * @param DOMElement $post 
     * @return void
     */
    protected function _setFromXmlElement(SimpleXMLElement $postElement)
    {
        if (isset($postElement->{'regular-title'})) {
            $this->setTitle((string) $postElement->{'regular-title'});
        }
        
        if (isset($postElement->{'regular-body'})) {
            $this->setBody((string) $postElement->{'regular-body'});
        }
    }
    
    /**
     * Title getter
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->_title;
    }
    
    /**
     * Title setter
     *
     * @param string $title 
     * @return void
     */
    public function setTitle($title)
    {
        $this->_title = $title;
    }
    
    /**
     * Body getter
     *
     * @return string|null
     */
    public function getBody()
    {
        return $this->_body;
    }
    
    /**
     * Body setter
     *
     * @param string $body 
     * @return void
     */
    public function setBody($body)
    {
        $this->_body = $body;
    }
    
    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            array(
                'title' => $this->getTitle(),
                'body' => $this->getBody()
            )
        );
    }
    
    /**
     * Builds params and makes requests
     *
     * @return bool
     */
    protected function _save()
    {
        $this->_setParameter('type', 'text');
        $this->_setParameter('title', $this->getTitle());
        $this->_setParameter('body', $this->getBody());
    }
}