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

class Runner
{
    public $aliases;
    public $script;
    public $vars;

    public function __construct(Script $script, $context=null)
    {
        $this->aliases = array();
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
                $this->vars['debug'] = $this->resolvePath($path);
                break;

            case 'debug_path_action':
                $path = array_shift($line);
                $action = array_shift($line);
                $this->vars['debug'] = $this->runPathAction($path, $action, $line);
                break;

            case 'debug_subpath':
                $path = $this->getArgumentPathValues($line);
                $this->vars['debug'] = $path[0];
                break;

            case 'new':
                $path = array_shift($line);
                $args = $this->getArgumentPathValues($line);
                /** @var ClassWrapper $class */
                $class = $this->resolvePath($path);
                $object = $class->newInstance($args);
                $this->vars['object'] = $object;
                $this->vars['last'] = $object;
                break;

            case 'call':
                $path = array_shift($line);
                $result = $this->runPathAction($path, self::PA_CALL, $line);
                $this->vars['result'] = $result;
                $this->vars['last'] = $result;
                break;

            case 'get':
                $path = array_shift($line);
                $value = $this->runPathAction($path, self::PA_GET);
                $this->vars['value'] = $value;
                $this->vars['last'] = $value;
                break;

            case 'set':
                $path = array_shift($line);
                $value = array_shift($line);
                $this->runPathAction($path, self::PA_SET, array($value));
                break;

            case 'use':
                $classPath = array_shift($line);
                /** @var ClassWrapper $class */
                $class = $this->resolvePath($classPath);
                $aliasName = (count($line) > 0) ? array_shift($line) : $class->getClassShortName();
                $this->aliases[$aliasName] = $classPath;
                break;

            case 'print':
                print $line[0];
                break;
        }
    }

    public function getArgumentPathValues($arguments)
    {
        foreach($arguments as $i=>$arg) {
            if (in_array(
                substr($arg, 0, 1),
                array('$', '!')) // TODO: Modular
            ) {
                $arguments[$i] = $this->runPathAction($arg, self::PA_GET);
            }
        }
        return $arguments;
    }

    const PA_GET    = 'CORE_GET';
    const PA_SET    = 'CORE_SET';
    const PA_CALL   = 'CORE_CALL';

    public function runPathAction($path, $action, $args=array())
    {
        if ( ! is_array($path)) {
            $path = explode('.', $path);
        }
        if ($action && count($path) > 1) {
            $name = end($path);
            $targetPath = array_slice($path, 0, -1);
        } else {
            $action = null;
            $name = null;
            $targetPath = $path;
        }
        $target = $this->resolvePath($targetPath);
        $args = $this->getArgumentPathValues($args);

        //
        // TODO: This should become modular hook responding to pairs of TargetClass:PathAction
        // NOTE: We don't know how to operate on the last path piece without a given action.
        //       I.E. Get is $obj->$field, set is $obj->field = $value.
        //

        switch ($action) {
            case self::PA_GET:
                return $target->$name;
            case self::PA_SET:
                $target->$name = $args[0];
                return null;
            case self::PA_CALL:
                return call_user_func_array(array($target, $name), $args);
                break;
        }

        return $target;
    }

    public function resolvePath($path)
    {
        if ( ! is_array($path)) {
            $path = explode('.', $path);
        }
        $pathString = implode('.', $path);

        // TODO: Path modifiers should be externalized as plugins.
        switch (substr($path[0], 0, 1)) {
            case '$':
                // Variable instance traversal.
                //
                $varName = ltrim(array_shift($path), '$');
                if ( ! array_key_exists($varName, $this->vars)) {
                    throw new Exception(
                        "Attempted to access undefined runtime variable. ($pathString)",
                        Exception::NOT_FOUND
                    );
                }
                $target = $this->vars[$varName];
                if ( ! $this->_traverseInstance($path, $target)) {
                    throw new Exception(
                        "Attempted to access undefined property. ($pathString)",
                        Exception::NOT_FOUND
                    );
                }
                return $target;

            case '!':
                // Class alias.
                //
                $aliasName = ltrim(array_shift($path), '!');
                if ( ! array_key_exists($aliasName, $this->aliases)) {
                    throw new Exception(
                        "Attempted to access undefined runtime alias. ($pathString)",
                        Exception::NOT_FOUND
                    );
                }
                $classPath = $this->aliases[$aliasName];
                return $this->resolvePath(rtrim($classPath . '.' . implode('.', $path), '.'));

            default:
                // Class traversal.
                //
                $classWrapper = $this->_traverseClass($path);
                if ( ! $classWrapper) {
                    throw new Exception(
                        "Attempted to access undefined class. ($pathString)",
                        Exception::NOT_FOUND
                    );
                }
                return $classWrapper;
        }
    }

    protected function _traverseInstance(array $path, &$target)
    {
        for ($i=0; $i<count($path); $i++) {
            if ( ! isset($target->$path[$i])) {
                return false;
            }
            $target = $target->$path[$i];
        }
        return true;
    }

    protected function _traverseClass(array $path)
    {
        $targetClass = implode('\\', $path);
        if (class_exists($targetClass)) {
            return new ClassWrapper($targetClass);
        }
        return null;
    }
}

// END of file
