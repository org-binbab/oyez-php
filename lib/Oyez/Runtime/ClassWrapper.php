<?php
/**
 * "Oyez" - The Universal Format Library
 *
 * @author      BinaryBabel OSS (<projects@binarybabel.org>)
 * @link        http://code.binbab.org
 * @copyright   Copyright 2013 sha1(OWNER) = df334a7237f10846a0ca302bd323e35ee1463931
 * @license     GNU Lesser General Public License v3 (see LICENSE and COPYING files)
 */

namespace Oyez\Runtime;
 
class ClassWrapper
{
    /** @var \ReflectionClass */
    protected $_reflection;

    public function __construct($class_name)
    {
        $this->_reflection = new \ReflectionClass($class_name);
    }

    public function newInstance($args=array())
    {
        return $this->_reflection->newInstanceArgs($args);
    }

    public function getClassName()
    {
        return $this->_reflection->getName();
    }

    public function getClassShortName()
    {
        return $this->_reflection->getShortName();
    }

    public function __toString()
    {
        return $this->getClassName();
    }

    public function __call($name, $args=array())
    {
        return $this->_reflection->getMethod($name)->invokeArgs(null, $args);
    }

    public function __get($name)
    {
        if ($this->_reflection->hasConstant($name)) {
            return $this->_reflection->getConstant($name);
        }
        return $this->_reflection->getProperty($name)->getValue(null);
    }

    public function __set($name, $value)
    {
        $this->_reflection->getProperty($name)->setValue(null, $value);
    }
}

// END of file
