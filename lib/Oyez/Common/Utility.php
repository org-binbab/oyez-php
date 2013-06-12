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
 
class Utility
{
    /**
     * Function to emulate array bracket syntax.
     *
     * PHP 5.3 does not support dereferencing arrays from function outputs.
     * This function provides a neutral workaround without having
     * to clutter code with temporary variables.
     *
     * @see array_slice()
     * @param array $array
     * @param int $index operates according to array_slice.
     * @return mixed
     */
    static public function array_getValueAt(array $array, $index)
    {
        $array = array_slice($array, $index, 1);
        return count($array) > 0 ? $array[0] : null;
    }

    /**
     * Function to proxy array end() actions.
     *
     * The end() function cannot be used on function outputs.
     * This function provides a neutral workaround without having
     * to clutter code with temporary variables.
     *
     * @param array $array
     * @return mixed
     */
    static public function array_getEndValue(array $array)
    {
        return end($array);
    }

    /**
     * Return short name of class using reflection.
     *
     * @param mixed $class_or_object
     * @throws Exception on missing or invalid class.
     * @return string
     */
    static public function class_getShortName($class_or_object)
    {
        $rClass = new \ReflectionClass($class_or_object);   // exception on unknown
        return $rClass->getShortName();
    }
}

// END of file
