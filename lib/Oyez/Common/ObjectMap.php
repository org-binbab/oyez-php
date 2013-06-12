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

class ObjectMap implements \Countable, \Iterator, \ArrayAccess
{
    protected $_next_key_id;
    protected $_key_objects;
    protected $_map_storage;

    public function __construct()
    {
        $this->_next_key_id = 1;
        $this->_key_objects = array();
        $this->_map_storage = array();
    }

    //
    // OBJECTS
    //////////////////////////////////////////////////////////////////////////////////////

    public function addObject($object)
    {
        if ($this->hasObject($object)) {
            throw new Exception(
                "Attempted to add duplicate object.",
                Exception::DUPLICATE
            );
        }
        $key = $this->_obj_next_key();
        $this->_key_objects[$key] = $object;
    }

    public function delObject($object)
    {
        $key = $this->_obj_find_key($object);
        if (false === $key) {
            throw new Exception(
                "Attempted to delete unknown object.",
                Exception::NOT_FOUND
            );
        }
        unset($this->_key_objects[$key]);
    }

    public function hasObject($object)
    {
        return (false !== $this->_obj_find_key($object));
    }

    public function getObjects()
    {
        return array_values($this->_key_objects);
    }

    public function getLastObject()
    {
        return end($this->_key_objects);
    }

    protected function _obj_find_key($object)
    {
        return array_search($object, $this->_key_objects, true);
    }

    protected function _obj_next_key()
    {
        return $this->_next_key_id++;
    }

    // ----------------------------------------------------------------------------------


    //
    // MAPS
    //////////////////////////////////////////////////////////////////////////////////////

    public function newMap($map_id)
    {
        if ($this->hasMap($map_id)) {
            throw new Exception(
                "Attempted to add new map with duplicate id.",
                Exception::DUPLICATE
            );
        }
        $this->_map_storage[$map_id] = array();
    }

    public function delMap($map_id)
    {
        if ( ! $this->hasMap($map_id)) {
            throw new Exception(
                "Attempted to delete unknown map.",
                Exception::NOT_FOUND
            );
        }
        unset($this->_map_storage[$map_id]);
    }

    public function hasMap($map_id)
    {
        return array_key_exists($map_id, $this->_map_storage);
    }

    public function getObjectMap($object)
    {
        if ( ! $this->hasObject($object)) {
            throw new Exception(
                "Attempted to map for unknown object.",
                Exception::NOT_FOUND
            );
        }
        $key = $this->_obj_find_key($object);
        $objMap = array();
        foreach ($this->_map_storage as $id => $data) {
            $objMap[$id] = array_key_exists($key, $data) ? $data[$key] : null;
        }
        return $objMap;
    }

    // ----------------------------------------------------------------------------------


    //
    // MAPPED VALUES
    //////////////////////////////////////////////////////////////////////////////////////

    public function getMappedValue($object, $map_id)
    {
        if ( ! $this->hasMappedValue($object, $map_id)) {
            return null;
        }
        $key = $this->_obj_find_key($object);
        return $this->_map_storage[$map_id][$key];
    }

    public function setMappedValue($object, $map_id, $value)
    {
        if ( ! $this->hasObject($object)) {
            throw new Exception(
                "Attempted to set value for unknown object.",
                Exception::NOT_FOUND
            );
        }
        if ( ! $this->hasMap($map_id)) {
            throw new Exception(
                "Attempted to set value for unknown map.",
                Exception::NOT_FOUND
            );
        }
        $key = $this->_obj_find_key($object);
        $this->_map_storage[$map_id][$key] = $value;
    }

    public function delMappedValue($object, $map_id)
    {
        if ($this->hasMappedValue($object, $map_id)) {
            $key = $this->_obj_find_key($object);
            unset($this->_map_storage[$map_id][$key]);
        }
    }

    public function hasMappedValue($object, $map_id)
    {
        if ( ! $this->hasObject($object)) {
            throw new Exception(
                "Attempted to get value for unknown object.",
                Exception::NOT_FOUND
            );
        }
        if ( ! $this->hasMap($map_id)) {
            throw new Exception(
                "Attempted to get value for unknown map.",
                Exception::NOT_FOUND
            );
        }
        $key = $this->_obj_find_key($object);
        return array_key_exists($key, $this->_map_storage[$map_id]);
    }

    // ----------------------------------------------------------------------------------


    //
    // PHP Core Interfaces
    //////////////////////////////////////////////////////////////////////////////////////

    protected $__iterate_current_key;

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return @$this->_key_objects[$this->__iterate_current_key];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $keys = array_keys($this->_key_objects);
        if ($this->valid()) {
            $i = array_search($this->__iterate_current_key, $keys);
            if ($i < $this->count()-1) {
                $this->__iterate_current_key = $keys[$i+1];
            } else {
                $this->__iterate_current_key = null;
            }
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->__iterate_current_key;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return (
            $this->__iterate_current_key !== null
            && array_key_exists($this->__iterate_current_key, $this->_key_objects)
        );
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        if ($this->count() > 0) {
            $this->__iterate_current_key = current(array_keys($this->_key_objects));
        }
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->_key_objects);
    }

    // ----------------------------------------------------------------------------------
    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return $this->hasObject($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->getObjectMap($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @throws \Oyez\Common\Exception
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->addObject($value);
        } else {
            throw new Exception("Only appending is permitted via array access.");
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->delObject($offset);
    }
}

// END of file
