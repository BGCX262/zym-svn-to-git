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
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
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
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Tumblr_Post_Photo extends Zend_Service_Tumblr_Post_Abstract
{    

    /**
     * Url / resource of photo
     *
     * @var string|null
     */
    protected $_source;
    
    /**
     * Source image filename
     *
     * @var string|null
     */
    protected $_filename;
    
    /**
     * Photo caption
     *
     * @var string|null
     */
    protected $_caption;
    
    /**
     * Click through url for photo
     *
     * @var string
     */
    protected $_linkUrl;
    
    /**
     * Photos in various sizes
     * 
     * @var array
     */
    protected $_photos = array();
    
    /**
     * Source getter
     *
     * @return string|null
     */
    public function getSource()
    {
        return $this->_source;
    }
    
    /**
     * Source setter
     *
     * @param string $source 
     * @return void
     */
    public function setSource($source)
    {
        $this->_source = $source;
    }
    
    /**
     * Filename getter
     *
     * @return string|null
     */
    public function getFilename()
    {
        return $this->_filename;
    }
    
    /**
     * Filename setter
     *
     * @param string $filename 
     * @return void
     */
    public function setFilename($filename)
    {
        $this->_filename = $filename;
    }
    
    /**
     * Caption getter
     *
     * @return string|null
     */
    public function getCaption()
    {
        return $this->_caption;
    }
    
    /**
     * Caption setter
     *
     * @return void
     */
    public function setCaption($caption)
    {
        $this->_caption = $caption;
    }
    
    /**
     * Click through url getter
     *
     * @return string|null
     */
    public function getLinkUrl()
    {
        return $this->_linkUrl;
    }
    
    /**
     * Click through url setter
     *
     * @param string $clickThroughUrl 
     * @return void
     */
    public function setLinkUrl($linkUrl)
    {
        $this->_linkUrl = $linkUrl;
    }
    
    /**
     * Photos getter
     * 
     * @var array
     */
    public function getPhotos()
    {
        return $this->_photos;
    }
    
    /**
     * Sets photo post specific properties
     *
     * @param DOMElement $post 
     * @return void
     */
    protected function _setFromXmlElement(SimpleXMLElement $postElement)
    {        
        if (isset($postElement->{'photo-caption'})) {
            $this->setCaption((string) $postElement->{'photo-caption'});
        }
        
        if (isset($postElement->{'photo-link-url'})) {
            $this->setLinkUrl((string) $postElement->{'photo-link-url'});
        }
        
        $this->_photos = array();
        if (isset($postElement->{'photo-url'})) {
            foreach ($postElement->{'photo-url'} as $photoUrl) {
                $this->_photos[(string) $photoUrl['max-width']] = (string) $photoUrl;
            }
        }
    }
    
    /**
     * Builds params and makes requests
     *
     * @return bool
     */
    protected function _save()
    {
        if ($this->getSource() === null && $this->getFilename() === null) {
            throw new Zend_Service_Exception("Missing required photo source or filename.");
        }
        
        $this->_setParameter('type', 'photo');
        $this->_setParameter('source', $this->getSource());
        $this->_setParameter('data', $this->getFilename());
        $this->_setParameter('caption', $this->getCaption());
        $this->_setParameter('click-through-url', $this->getLinkUrl());
    }
}