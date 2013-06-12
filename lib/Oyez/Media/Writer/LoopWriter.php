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
 
use Oyez\Common\Buffer;
use Oyez\Media\Writer;

class LoopWriter extends Writer
{
    /** @var Buffer */
    public $buffer;

    /** @codeCoverageIgnore */
    public function is_open()
    {
        return (bool)$this->buffer;
    }

    /** @codeCoverageIgnore */
    protected function _open()
    {
        $this->buffer = new Buffer();
    }

    /** @codeCoverageIgnore */
    protected function _write($content)
    {
        $this->buffer->write($content);
    }

    /** @codeCoverageIgnore */
    protected function _close()
    {
        $this->buffer = null;
    }
}

// END of file
