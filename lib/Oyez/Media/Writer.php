<?php
/**
 * "Oyez" - The Universal Format Library
 *
 * @author      BinaryBabel OSS (<projects@binarybabel.org>)
 * @link        http://code.binbab.org
 * @copyright   Copyright 2013 sha1(OWNER) = df334a7237f10846a0ca302bd323e35ee1463931
 * @license     GNU Lesser General Public License v3 (see LICENSE and COPYING files)
 */

namespace Oyez\Media;

use Oyez\Media\Exception;
 
abstract class Writer
{
    public function open()
    {
        if ( ! $this->is_open()) {
            $this->_open();
        }
        if ( ! $this->is_open()) {
            throw new Exception("Writer failed to open.");
        }
    }

    public function write($content)
    {
        if ( ! $this->is_open()) {
            throw new Exception("Attempted to write to closed writer.");
        }

        $this->_write($content);
    }

    public function close()
    {
        if ($this->is_open()) {
            $this->_close();
        }
        if ($this->is_open()) {
            throw new Exception("Writer failed to close.");
        }
    }

    /** @codeCoverageIgnore */
    abstract public function is_open();

    /** @codeCoverageIgnore */
    abstract protected function _open();

    /** @codeCoverageIgnore */
    abstract protected function _write($content);

    /** @codeCoverageIgnore */
    abstract protected function _close();
}

// END of file
