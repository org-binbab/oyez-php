<?php
/**
 * "Oyez" - The Universal Format Library
 *
 * @author      BinaryBabel OSS (<projects@binarybabel.org>)
 * @link        http://code.binbab.org
 * @copyright   Copyright 2013 sha1(OWNER) = df334a7237f10846a0ca302bd323e35ee1463931
 * @license     GNU Lesser General Public License v3 (see LICENSE and COPYING files)
 */

namespace Oyez\Format;

use Oyez\Common\Exception;

class Text
{
    public $column_width;

    public function __construct($column_width=90)
    {
        $this->column_width = $column_width;
    }

    /**
     * Calculate width in reference to column width as follows:
     *   >0  positive value returned as inputted
     *   =0  exact column width returned
     *   <0  negative value added to column width
     *
     * @param int $width
     * @return int
     */
    protected function _calculateWidth($width)
    {
        $width = ($width && is_int($width)) ? $width : $this->column_width;
        if ($width < 1) {
            $width = ($this->column_width - abs($width));
        } elseif ($width > 0) {
            // No action needed.
            // Braced for coverage tests.
        }
        return $width;
    }

    const ALIGN_LEFT   = -1;
    const ALIGN_CENTER =  0;
    const ALIGN_RIGHT  =  1;

    public function align($align, $string, $pad_char=' ', $width=0)
    {
        $width = $this->_calculateWidth($width);
        $strLn = mb_strlen($string);
        if ($strLn < $width) {
            switch ($align) {
                case self::ALIGN_LEFT:
                    $string = sprintf("%'" . $pad_char . "-" . $width . 's', $string);
                    break;

                case self::ALIGN_RIGHT:
                    $string = sprintf("%'" . $pad_char . $width . 's', $string);
                    break;

                case self::ALIGN_CENTER:
                    $padWidth = ($width - $strLn);
                    $padLeft  = floor($padWidth / 2);
                    $padRight = ceil($padWidth / 2);
                    $string = implode('', array(
                        sprintf("%'" . $pad_char . $padLeft . 's', ''),
                        $string,
                        sprintf("%'" . $pad_char . $padRight . 's', '')
                    ));
                    break;
            }
        }
        return $string;
    }

    public function alignCenter($string, $pad_char=' ', $width=0)
    {
        return $this->align(self::ALIGN_CENTER, $string, $pad_char, $width);
    }

    public function alignLeft($string, $pad_char=' ', $width=0)
    {
        return $this->align(self::ALIGN_LEFT, $string, $pad_char, $width);
    }

    public function alignRight($string, $pad_char=' ', $width=0)
    {
        return $this->align(self::ALIGN_RIGHT, $string, $pad_char, $width);
    }

    public $cut_marker =  '...';

    /**
     * Truncate string at column width (or given length).
     *
     * @param string $string
     * @param int $max_width
     * @param bool $pad
     * @return string
     */
    public function cut($string, $max_width=0, $pad=true)
    {
        $max_width = $this->_calculateWidth($max_width);

        $cutString = substr($string, 0, $max_width);
        if ($pad) {
            $cutString = sprintf('%-' . $max_width . 's', $cutString);
        }

        return $cutString;
    }

    /**
     * Truncate string and append configured marker.
     * Width includes length of marker.
     *
     * @param string $string
     * @param int $max_width
     * @param bool $pad
     * @return string
     */
    public function cut_mark($string, $max_width=0, $pad=true)
    {
        $max_width = $this->_calculateWidth($max_width);

        if (mb_strlen($string) <= $max_width) {
            return $this->cut($string, $max_width, $pad);
        }

        $marker = $this->cut_marker;
        $marketLn = mb_strlen($marker);
        return substr($string, 0, ($max_width - $marketLn)) . $marker;
    }

    /**
     * Pad string with spaces to minimum width.
     *
     * @param string $string
     * @param int $width
     * @return string
     */
    public function pad($string, $width=0)
    {
        return $this->alignLeft($string, ' ', $width);
    }

    public $indent_space = '    ';

    /**
     * Indent text block by desired amount.
     *
     * @param string $string
     * @param int $count
     * @return string
     */
    public function indent($string, $count)
    {
        $count = max($count, 1);
        $_spacer = '';
        for ($i=0; $i<$count; $i++) {
            $_spacer .= $this->indent_space;
        }

        return (string) preg_replace('/^\s*/m', $_spacer, $string);
    }

    const LIST_NUM = 1;
    const LIST_ABC = 2;
    const LIST_IVX = 3;
    const LIST_LOWERCASE = 10;

    protected $_list_abc = 'abcdefghijklmnopqrstuvwxyz';

    public $list_index_space = 4;
    public $list_force_width = true;

    /**
     * Return index in various formats, including numeric, alphabetic, and roman.
     * Uppercase by default, but lowercase can be selected via an added modifier.
     *
     * @param int $list_type (LIST_NUM, LIST_ABC, LIST_IVX) + LIST_LOWERCASE
     * @param int $i
     * @return string
     */
    public function list_index($list_type, $i)
    {
        $uppercase = ! intval($list_type/self::LIST_LOWERCASE);
        $list_type = $list_type%10;

        $i = ($i > 0) ? intval($i) : 1;
        $i = ($i < 1000) ? $i : 999;

        $fmtIndex = null;
        switch($list_type) {
            case self::LIST_ABC:
                $i--;
                $fmtIndex = '';
                foreach (array(
                    intval($i/702)-1,
                    intval(($i%702)/26)-($i < 702 ? 1 : 0),
                    intval($i%26),
                ) as $i) {
                    if ($i >= 0) {
                        $fmtIndex .= substr($this->_list_abc, $i, 1);
                    }
                }
                break;

            case self::LIST_IVX:
                $fmtIndex = '';
                $map = array('', 'c', 'cc', 'ccc', 'cd', 'd', 'dc', 'dcc', 'dccc', 'cm');
                $fmtIndex .= $map[intval($i/100)];
                $map = array('', 'x', 'xx', 'xxx', 'xl', 'l', 'lx', 'lxx', 'lxxx', 'xc');
                $fmtIndex .= $map[intval(($i%100)/10)];
                $map = array('', 'i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix');
                $fmtIndex .= $map[intval($i%10)];
                break;

            case self::LIST_NUM:
            default:
                $fmtIndex = $i;
        }
        $fmtIndex = $uppercase ? strtoupper($fmtIndex) : strtolower($fmtIndex);

        return sprintf('%' . $this->list_index_space . 's', $fmtIndex);
    }

    /**
     * Titled list item, unordered by default.
     * For unordered (bullet) lists, the list_type should be a string.
     * For ordered lists, the list_type should be a list style option.
     * See list_index() for list style options.
     *
     * @see Plain::list_index()
     * @param string $title
     * @param mixed $list_type
     * @param int $i
     * @param string $i_prefix
     * @param string $i_suffix
     * @return string
     */
    public function list_item($title, $list_type='+', $i=0, $i_prefix='', $i_suffix='')
    {
        if (is_int($list_type)) {
            $fmtIndex = $i_prefix . trim($this->list_index($list_type, $i)) . $i_suffix;
            $fmtIndex = sprintf(
                '%' . ($this->list_index_space + mb_strlen($i_prefix) + mb_strlen($i_suffix)) . 's',
                $fmtIndex
            );
        } else {
            $fmtIndex = trim($i_prefix . $list_type . $i_suffix);
        }

        $fmtIndex .= ' ';
        if ($this->list_force_width) {
            $title = $this->cut_mark($title, -mb_strlen($fmtIndex));
        }
        return $fmtIndex . $title . "\n";
    }

    /**
     * Line separator using given character.
     *
     * @param string $c
     * @param int $width
     * @return string
     */
    public function separator($c='-', $width=0)
    {
        $width = $this->_calculateWidth($width);
        return sprintf("%'" . $c . '-' . $width . 's', '') . "\n";
    }
}

// END of file
