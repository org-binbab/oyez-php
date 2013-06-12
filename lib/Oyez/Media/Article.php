<?php
/**
 * "Oyez" - The Universal Format Library
 *
 * @author      BinaryBabel OSS (<projects@binarybabel.org>)
 * @link        http://code.binbab.org
 * @copyright   Copyright 2013 sha1(OWNER) = df334a7237f10846a0ca302bd323e35ee1463931
 * @license     GNU Lesser General Public License v3 (see LICENSE and COPYING files)
 */

namespace Oyez\Media;

use Oyez\Common\Exception;
use Oyez\Common\Object;
use Oyez\Media\Article\Edition;

/**
 * @property bool   $closed
 * @property Editor $editor
 * @property bool   $notify_editor
 * @property-read   Article $parent
 * @property-read   array   $sub_articles
 * @property string $title
 */
abstract class Article extends Object
{
    protected $__field_prefix = '_';
    protected $__locked_fields;
    protected $__nonotify_fields;
    protected $__unclosed_fields;

    /** @var bool */
    protected $_closed;
    /** @var string */
    protected $_title;
    /** @var Editor */
    protected $_editor;
    /** @var bool */
    protected $_notify_editor = true;
    /** @var Article */
    protected $_parent;
    /** @var array */
    protected $_sub_articles;

    public function __construct($title, Editor $editor=null, Article $parent=null)
    {
        $this->__locked_fields = array('parent', 'sub_articles');
        $this->__nonotify_fields = array('parent');
        $this->__unclosed_fields = array('editor');

        $this->_closed = false;
        $this->_editor = $editor;
        $this->_sub_articles = array();

        if ($parent) {
            if ($parent->closed) {
                throw new Exception("Attempted add sub-article to closed article.", Exception::READ_ONLY);
            }
            $this->_parent = $parent;
            $parent->_sub_articles[] = $this;
            $parent->_notifyEditor('sub_articles');
        }

        $this->title = $title;
    }

    public function __get($key)
    {
        return parent::__get($key);
    }

    public function __set($key, $value)
    {
        try {
            if ($this->$key === $value) {
                return;     // do nothing if value unchanged
            }
        } catch(\Exception $e) {
        }

        if (
            $this->_closed
            && is_array($this->__unclosed_fields)
            && ! in_array($key, $this->__unclosed_fields)
        ) {
            throw new Exception("Attempted to set property on closed article.", Exception::READ_ONLY);
        }

        if ($this->isFieldLocked($key)) {
            throw new Exception("Attempted to set locked field. ($key)", Exception::READ_ONLY);
        }

        if ('closed' == $key) {
            try {
                $this->closeSubArticles();
            } catch(Exception $e) {
            }
            $this->_enforceClosedSubArticles();
            $this->_notifyEditor($key);
            $this->_enforceRequiredFields();
            $this->_closed = true;
            $this->_notifyEditor($key);
        } else {
            parent::__set($key, $value);    // exception on missing field
            $this->_notifyEditor($key);
        }
    }

    public function lockField($field)
    {
        $this->__locked_fields[$field] = true;
    }

    public function isFieldLocked($field)
    {
        return (
            is_array($this->__locked_fields)
            && array_key_exists($field, $this->__locked_fields)
            && $this->__locked_fields[$field]
        );
    }

    public function getDepth()
    {
        $depth = 0;
        if ($this->_parent) {
            $depth++;
            $depth += $this->_parent->getDepth();
        }
        return $depth;
    }

    public function hasOpenSubArticles()
    {
        if ( ! empty($this->_sub_articles)) {
            foreach ($this->_sub_articles as $article) {
                if ( ! $article->closed) {
                    return true;
                }
            }
        }
        return false;
    }

    public function closeSubArticles()
    {
        if (is_array($this->_sub_articles)) {
            foreach ($this->_sub_articles as $article) {
                if ( ! $article->closed) {
                    $article->closed = true;
                }
            }
        }
    }

    /**
     * @param string $class
     * @return Edition
     * @throws \Oyez\Common\Exception
     */
    public function newEdition($class='')
    {
        if ($this->editor) {
            if (empty($class)) {
                $class = sprintf('%s_%sEdition', get_class($this), $this->editor->type);
            } else {
                // Coverage.
            }
            if ( ! class_exists($class)) {
                throw new Exception("Unknown article edition. ($class)", Exception::NOT_FOUND);
            }
            $rClass = new \ReflectionClass($class);
            if ( ! $rClass->isSubclassOf(__CLASS__ . '\Edition')) {
                throw new Exception("Invalid article edition. ($class)", Exception::BAD_VALUE);
            }
            return $rClass->newInstance($this, $this->editor);
        }
        throw new Exception("Attempted to get article edition without an assigned editor.");
    }

    /** @codeCoverageIgnore */
    abstract public function getRequiredFields();

    protected function _enforceClosedSubArticles()
    {
        if ($this->hasOpenSubArticles()) {
            throw new Exception("Attempted to close article with open sub-articles.");
        }
    }

    protected function _enforceRequiredFields()
    {
        $reqFields = $this->getRequiredFields();
        if (is_array($reqFields)) {
            foreach ($reqFields as $field) {
                if (null === $this->$field) {
                    throw new Exception(
                        "Article is missing required field. ($field)",
                        Exception::BAD_VALUE
                    );
                }
            }
        }
    }

    protected function _notifyEditor($field)
    {
        if ($this->editor && $this->notify_editor) {
            if( ! (
                is_array($this->__nonotify_fields)
                && in_array($field, $this->__nonotify_fields)
            )) {
                $this->editor->article_updated($this, $field);
            }
        }
    }
}

// END of file
