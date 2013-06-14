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

use Oyez\Common\Exception;

class Runner
{
    public $script;
    public $vars;

    public function __construct(Script $script, $context=null)
    {
        $this->script = $script;
        $this->_initVars();
        $this->_initContext($context);
        $this->_executeMain();
    }

    protected function _initVars()
    {
        $this->vars = array();
        $this->vars['this'] = new \StdClass();
        $this->vars['last'] = null;
        $this->vars['value'] = null;
        $this->vars['object'] = null;
        $this->vars['result'] = null;

    }

    protected function _initContext($context=null)
    {
        if (is_object($context)) {
            $this->vars['this'] = $context;
        } elseif (is_array($context)) {
            foreach ($context as $key=>$val) {
                $this->vars['this']->$key = $val;
            }
        }
    }

    protected function _executeMain()
    {
        for ($i=0; $i<count($this->script->main); $i++) {
            $line = $this->script->main[$i];
            $this->_executeLine($line);
        }
    }

    protected function _executeLine(array $line)
    {
        $cmd = array_shift($line);
        switch ($cmd) {
            case 'debug_path':
                $path = array_shift($line);
                $this->vars['debug'] = $this->_traverse($path);
                break;

            case 'new':
                $class = array_shift($line);
                $class = $this->_traverse($class);
                $rClass = new \ReflectionClass($class);
                $object = $rClass->newInstanceArgs($line);
                $this->vars['object'] = $object;
                $this->vars['last'] = $object;
                break;

            case 'call':
                $targetPath = array_shift($line);
                $target = $this->_traverse($targetPath, true, $method);
                $rClass = new \ReflectionClass($target);
                $rMethod = $rClass->getMethod($method);
                $result = $rMethod->invokeArgs(
                    is_object($target) ? $target : null,
                    $line
                );
                $this->vars['result'] = $result;
                $this->vars['last'] = $result;
                break;

            case 'get':
                $targetPath = array_shift($line);
                $target = $this->_traverse($targetPath, true, $method);
                $rClass = new \ReflectionClass($target);
                $rProperty = $rClass->getProperty($method);
                $value = $rProperty->getValue(
                    is_object($target) ? $target : null
                );
                $this->vars['value'] = $value;
                $this->vars['last'] = $value;
                break;

            case 'set':
                $targetPath = array_shift($line);
                $target = $this->_traverse($targetPath, true, $method);
                $rClass = new \ReflectionClass($target);
                $rProperty = $rClass->getProperty($method);
                $value = array_shift($line);
                $rProperty->setValue(
                    is_object($target) ? $target : null,
                    $value
                );
                break;

            case 'print':
                print $line[0];
                break;
        }
    }

    protected function _traverse($path, $pop=false, &$pop_out=null)
    {
        $pathString = $path;
        $path = explode('.', $path);
        if ($pop) {
            $pop_out = array_pop($path);
        }

        if (substr($path[0], 0, 1) == '$') {
            // Instance traversal.
            //
            $path[0] = substr($path[0], 1);
            if ( ! array_key_exists($path[0], $this->vars)) {
                throw new Exception(
                    "Attempted to access undefined runtime variable. ({$path[0]})",
                    Exception::NOT_FOUND
                );
            }
            $root = $this->vars[$path[0]];
            $target = $root;
            for ($i=1; $i<count($path); $i++) {
                if ( ! isset($target->$path[$i])) {
                    throw new Exception(
                        "Attempted to access undefined property. ($pathString)",
                        Exception::NOT_FOUND
                    );
                }
                $target = $target->$path[$i];
            }
            return $target;
        } else {
            // Static traversal.
            //
            $targetClass = implode('\\', $path);
            if (class_exists($targetClass)) {
                return $targetClass;
            }
            throw new Exception(
                "Attempted to access undefined class. ($pathString)",
                Exception::NOT_FOUND
            );
        }
    }
}

// END of file
