<?php
/**
 * "Oyez" - The Universal Format Library
 *
 * @author      BinaryBabel OSS (<projects@binarybabel.org>)
 * @link        http://code.binbab.org
 * @copyright   Copyright 2013 sha1(OWNER) = df334a7237f10846a0ca302bd323e35ee1463931
 * @license     GNU Lesser General Public License v3 (see LICENSE and COPYING files)
 */

namespace Oyez\Common;
 
class Buffer
{
    /** @var string */
    protected $_buffer;
    /** @var int */
    protected $_offset;

    public function __construct()
    {
        $this->clear();
    }

    /**
     * Erase buffer contents and reset offset marker.
     */
    public function clear()
    {
        $this->_buffer = '';
        $this->_offset = 0;
    }

    /**
     * Return entire contents of buffer, regardless of offset marker.
     *
     * @return string
     */
    public function get()
    {
        return $this->_buffer;
    }

    /**
     * Return contents of buffer from offset marker.
     * If offset marker is not provided, last position is used.
     * Offset is advanced to end of buffer.
     *
     * @param int|null $offset
     * @return string|null
     */
    public function read($offset=null)
    {
        $offset = is_int($offset) ? $offset : $this->_offset;
        $buffer_bytes = strlen($this->_buffer);

        if ($buffer_bytes == 0) {
            return null;
        }

        if ($offset >= $buffer_bytes) {
            $this->_offset = $buffer_bytes;
            return null;
        }

        $output = substr($this->_buffer, $offset);
        $this->_offset = $buffer_bytes;

        return $output;
    }

    /**
     * Write content to buffer.
     *
     * @param string $content
     */
    public function write($content)
    {
        $this->_buffer .= $content;
    }
}

// END of file
