<?php
/**
 * "Oyez" - The Universal Format Library
 *
 * @author      BinaryBabel OSS (<projects@binarybabel.org>)
 * @link        http://code.binbab.org
 * @copyright   Copyright 2013 sha1(OWNER) = df334a7237f10846a0ca302bd323e35ee1463931
 * @license     GNU Lesser General Public License v3 (see LICENSE and COPYING files)
 */

namespace Oyez\Media\Writer;

use Oyez\Media\Writer;
 
class StandardWriter extends Writer
{
    protected $_handle;

    public function is_open()
    {
        return ('resource' == gettype($this->_handle));
    }

    protected function _open()
    {
        // Standard output in PHP is already open.
        $this->_handle = STDOUT;
    }

    protected function _write($content)
    {
        defined(__CLASS__ . '\USE_PRINT')
            ? print $content
            : fwrite($this->_handle, $content);
    }

    protected function _close()
    {
        // No need to close standard output in PHP.
        $this->_handle = null;
    }
}

// END of file
