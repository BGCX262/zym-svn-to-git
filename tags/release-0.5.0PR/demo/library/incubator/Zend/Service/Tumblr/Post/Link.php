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
class Zend_Service_Tumblr_Post_Link extends Zend_Service_Tumblr_Post_Abstract
{
    /**
     * Link name (optional)
     *
     * @var string|null
     */
    protected $_name;

    /**
     * Link url
     *
     * @var string|null
     */
    protected $_linkUrl;

    /**
     * Link description (optional)
     *
     * @var string|null
     */
    protected $_description;

    /**
     * Sets text post specific properties
     *
     * @param DOMElement $post
     * @return void
     */
    protected function _setFromXmlElement(SimpleXMLElement $postElement)
    {
        if (isset($postElement->{'link-url'})) {
            $this->setLinkUrl((string) $postElement->{'link-url'});
        }

        if (isset($postElement->{'link-text'})) {
            $this->setName((string) $postElement->{'link-text'});
        }

        if (isset($postElement->{'link-description'})) {
            $this->setDescription((string) $postElement->{'link-description'});
        }
    }

    /**
     * Name getter
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Name setter
     *
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Link Url getter
     *
     * @return string|null
     */
    public function getLinkUrl()
    {
        return $this->_linkUrl;
    }

    /**
     * Link Url setter
     *
     * @param string $linkUrl
     * @return void
     */
    public function setLinkUrl($linkUrl)
    {
        $this->_linkUrl = $linkUrl;
    }

    /**
     * Description getter
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Description setter
     *
     * @param string $description
     * @return void
     */
    public function setDescription($description)
    {
        $this->_description = $description;
    }

    /**
     * Create Link Post data array
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            array(
                'name' => $this->getName(),
                'url' => $this->getLinkUrl(),
                'description' => $this->getDescription()
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
        $this->_setParameter('type', 'link');
        $this->_setParameter('name', $this->getName());
        $this->_setParameter('url', $this->getLinkUrl());
        $this->_setParameter('description', $this->getDescription());
    }
}