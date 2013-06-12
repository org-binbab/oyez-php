<?php
/**
 * "Oyez" - The Universal Format Library
 *
 * @author      BinaryBabel OSS (<projects@binarybabel.org>)
 * @link        http://code.binbab.org
 * @copyright   Copyright 2013 sha1(OWNER) = df334a7237f10846a0ca302bd323e35ee1463931
 * @license     GNU Lesser General Public License v3 (see LICENSE and COPYING files)
 */

namespace Oyez\Media\Article\Edition;
 
use Oyez\Media\Article\Edition;

abstract class SimpleEdition extends Edition
{
    private $_rendered = false;

    /** @codeCoverageIgnore */
    abstract protected function _render();

    public function field($field)
    {
        if ($field == "sub_articles" && ! $this->_rendered) {
            $this->_rendered = true;
            $this->_render();
        }
    }

    public function closed()
    {
        if ( ! $this->_rendered) {
            $this->_rendered = true;
            $this->_render();
        }
    }
}

// END of file
