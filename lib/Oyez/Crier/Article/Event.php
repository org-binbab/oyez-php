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
 * @property int $status
 * @property-read string $status_human
 */
class Event extends Article
{
    const STATUS_UNKNOWN = -1;
    const STATUS_SUCCESS =  0;
    const STATUS_FAILURE =  1;
    const STATUS_WARNING =  2;
    const STATUS_SKIPPED =  3;

    protected $_status;

    protected function __get_status_human()
    {
        $map = array(
            Event::STATUS_SUCCESS => 'OK',
            Event::STATUS_FAILURE => 'Fail',
            Event::STATUS_WARNING => 'Warn',
            Event::STATUS_SKIPPED => 'Skip',
        );
        $status = $this->_status;
        return array_key_exists($status, $map) ? $map[$status] : '';
    }

    public function getRequiredFields()
    {
        return array('status');
    }
}

// END of file
