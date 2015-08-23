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
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Mcrypt.php 21212 2010-02-27 17:33:27Z thomas $
 */

/**
 * @see Zend_Filter_Encode_EncodeInterface
 */
require_once 'Zend/Filter/Encode/EncodeInterface.php';

/**
 * Encode adapter for Punycode
 *
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_Encode_Punycode implements Zend_Filter_Encode_EncodeInterface
{
    /**
     * Encodes $value with the defined settings
     *
     * @param  string $value The content to encoded
     * @return string The encoded content
     */
    public function encode($value)
    {
        return base64_encode($value);
    }

    /**
     * Decodes $value with the defined settings
     *
     * @param  string $value Content to decode
     * @return string The decoded content
     */
    public function decode($value)
    {
        $found = preg_match('/([^a-z0-9\x2d]{1,10})$/i', $value);
        if (empty($value) || ($found > 0)) {
            // no punycode encoded string, return as is
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('The given string can not be decoded as Punycode');
        }

        $separator = strrpos($value, '-');
        if ($separator > 0) {
            for ($x = 0; $x < $separator; ++$x) {
                // prepare decoding matrix
                $decoded[] = ord($value[$x]);
            }
        } else {
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('The given string can not be decoded as Punycode');
        }

        $lengthd = count($decoded);
        $lengthe = strlen($value);

        // decoding
        $init  = true;
        $base  = 72;
        $index = 0;
        $char  = 0x80;

        for ($indexe = ($separator) ? ($separator + 1) : 0; $indexe < $lengthe; ++$lengthd) {
            for ($old_index = $index, $pos = 1, $key = 36; 1 ; $key += 36) {
                $hex   = ord($value[$indexe++]);
                $digit = ($hex - 48 < 10) ? $hex - 22
                       : (($hex - 65 < 26) ? $hex - 65
                       : (($hex - 97 < 26) ? $hex - 97
                       : 36));

                $index += $digit * $pos;
                $tag    = ($key <= $base) ? 1 : (($key >= $base + 26) ? 26 : ($key - $base));
                if ($digit < $tag) {
                    break;
                }

                $pos = (int) ($pos * (36 - $tag));
            }

            $delta   = intval($init ? (($index - $old_index) / 700) : (($index - $old_index) / 2));
            $delta  += intval($delta / ($lengthd + 1));
            for ($key = 0; $delta > 910 / 2; $key += 36) {
                $delta = intval($delta / 35);
            }

            $base   = intval($key + 36 * $delta / ($delta + 38));
            $init   = false;
            $char  += (int) ($index / ($lengthd + 1));
            $index %= ($lengthd + 1);
            if ($lengthd > 0) {
                for ($i = $lengthd; $i > $index; $i--) {
                    $decoded[$i] = $decoded[($i - 1)];
                }
            }

            $decoded[$index++] = $char;
        }

        // convert decoded ucs4 to utf8 string
        foreach ($decoded as $key => $value) {
            if ($value < 128) {
                $decoded[$key] = chr($value);
            } elseif ($value < (1 << 11)) {
                $decoded[$key]  = chr(192 + ($value >> 6));
                $decoded[$key] .= chr(128 + ($value & 63));
            } elseif ($value < (1 << 16)) {
                $decoded[$key]  = chr(224 + ($value >> 12));
                $decoded[$key] .= chr(128 + (($value >> 6) & 63));
                $decoded[$key] .= chr(128 + ($value & 63));
            } elseif ($value < (1 << 21)) {
                $decoded[$key]  = chr(240 + ($value >> 18));
                $decoded[$key] .= chr(128 + (($value >> 12) & 63));
                $decoded[$key] .= chr(128 + (($value >> 6) & 63));
                $decoded[$key] .= chr(128 + ($value & 63));
            } else {
                require_once 'Zend/Filter/Exception.php';
                throw new Zend_Filter_Exception('The given string can not be decoded as Punycode');
            }
        }

        return implode($decoded);
    }

    /**
     * Returns the adapter name
     *
     * @return string
     */
    public function toString()
    {
        return 'Punycode';
    }
}
