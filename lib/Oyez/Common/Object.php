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

use Oyez\Common\Exception;
 
class Object extends \stdClass
{
    protected $__field_prefix = null;

    public function __get($key)
    {
        $key = ltrim($key, '_');
        $get = "__get_$key";
        if (method_exists($this, $get)) {
            return $this->$get();
        }

        if ($this->__field_prefix) {
            $field = $this->__field_prefix . $key;
            if (property_exists($this, $field)) {
                return $this->$field;
            }
        }

        throw new Exception("Attempted to get missing property. ($key)", Exception::NOT_FOUND);
    }

    public function __set($key, $value)
    {
        $key = ltrim($key, '_');
        $set = "__set_$key";
        if (method_exists($this, $set)) {
            $this->$set($value);
            return null;
        }

        if ($this->__field_prefix) {
            $field = $this->__field_prefix . $key;
            if (property_exists($this, $field)) {
                $this->$field = $value;
                return null;
            }
        }

        try {
            $this->$key;
        } catch (\Exception $e) {
            throw new Exception("Attempted to set missing property. ($key)", Exception::NOT_FOUND);
        }

        throw new Exception("Attempted to set read-only property. ($key)", Exception::READ_ONLY);
    }
}

// END of file
