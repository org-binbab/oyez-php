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
 
class Exception extends \Exception
{
    const BAD_VALUE = 400;
    const NOT_FOUND = 404;
    const READ_ONLY = 405;
    const DUPLICATE = 409;
}

// END of file
