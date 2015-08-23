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
class Zend_Service_Tumblr_Post_Quote extends Zend_Service_Tumblr_Post_Abstract
{
    /**
     * Post title
     *
     * @var string|null
     */
    protected $_quote;

    /**
     * Post body content
     *
     * @var string|null
     */
    protected $_source;

    /**
     * Sets text post specific properties
     *
     * @param DOMElement $post
     * @return void
     */
    protected function _setFromXmlElement(SimpleXMLElement $postElement)
    {
        if (isset($postElement->{'quote-text'})) {
            $this->setQuote((string) $postElement->{'quote-text'});
        }

        if (isset($postElement->{'quote-source'})) {
            $this->setSource((string) $postElement->{'quote-source'});
        }
    }

    /**
     * Quote getter
     *
     * @return string|null
     */
    public function getQuote()
    {
        return $this->_quote;
    }

    /**
     * Quote setter
     *
     * @param string $quote
     * @return void
     */
    public function setQuote($quote)
    {
        $this->_quote = $quote;
    }

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
     * Create Quote Post array
     * 
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            array(
                'quote' => $this->getQuote(),
                'source' => $this->getSource()
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
        $this->_setParameter('type', 'quote');
        $this->_setParameter('quote', $this->getQuote());
        $this->_setParameter('source', $this->getSource());
    }
}