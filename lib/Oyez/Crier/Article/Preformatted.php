<?php
/**
 * "Oyez" - The Universal Format Library
 *
 * @author      BinaryBabel OSS (<projects@binarybabel.org>)
 * @link        http://code.binbab.org
 * @copyright   Copyright 2013 sha1(OWNER) = df334a7237f10846a0ca302bd323e35ee1463931
 * @license     GNU Lesser General Public License v3 (see LICENSE and COPYING files)
 */

namespace Oyez\Crier\Article;
 
use Oyez\Media\Article;

/**
 * @property string $content
 */
class Preformatted extends Article
{
    protected $_content;

    public function getRequiredFields()
    {
        return array('content');
    }
}

// END of file
