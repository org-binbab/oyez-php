<?php
/**
 * "Oyez" - The Universal Format Library
 *
 * @author      BinaryBabel OSS (<projects@binarybabel.org>)
 * @link        http://code.binbab.org
 * @copyright   Copyright 2013 sha1(OWNER) = df334a7237f10846a0ca302bd323e35ee1463931
 * @license     GNU Lesser General Public License v3 (see LICENSE and COPYING files)
 */

namespace Oyez;

use Oyez\Common\Object;
use Oyez\Crier\Exception;
use Oyez\Crier\Article\Event;
use Oyez\Crier\Article\Preformatted;
use Oyez\Media\Article;
use Oyez\Media\Editor;
use Oyez\Media\Writer;

/**
 * @property-read Editor $editor
 * @property-read Writer $writer
 */
class Crier extends Object
{
    public $article_namespace;
    public $auto_flush_buffer = true;

    /** @var Article */
    protected $_article_parent;
    /** @var Editor */
    protected $_editor;
    /** @var Writer */
    protected $_writer;

    public static $default_editor_class = '\Oyez\Media\Editor\TextEditor';
    public static $default_writer_class = '\Oyez\Media\Writer\StandardWriter';
    public static $default_article_namespace = '\Oyez\Crier\Article';

    public function __construct(Editor $editor=null, Writer $writer=null)
    {
        $this->article_namespace = static::$default_article_namespace;
        $this->_editor = is_object($editor) ? $editor : $this->_getDefaultEditor();
        $this->_editor->event_addListener(array($this, '_editor_callback'));
        $this->_writer = is_object($writer) ? $writer : $this->_getDefaultWriter();
        $this->_writer->open();
    }

    public function __destruct()
    {
        if ($this->_editor && $this->_writer) {
            $this->flushBuffer();
        }
        if ($this->_writer) {
            $this->_writer->close();
        }
    }

    protected function __get_editor()
    {
        return $this->_editor;
    }

    protected function __get_writer()
    {
        return $this->_writer;
    }

    /**
     * @param string $article_class
     * @param string $title
     * @return Article
     * @throws Crier\Exception
     */
    public function newArticle($article_class, $title)
    {
        if (substr($article_class, 0, 1) != '\\') {
            $article_class = $this->article_namespace . "\\$article_class";
        }
        if ( ! class_exists($article_class)) {
            throw new Exception(
                "Attempted to create an article from unknown class. ($article_class)",
                Exception::NOT_FOUND
            );
        }

        $rClass = new \ReflectionClass($article_class);
        if ( ! $rClass->isSubclassOf('\Oyez\Media\Article')) {
            throw new Exception(
                "Attempted to create an article from invalid class. ($article_class)",
                Exception::BAD_VALUE
            );
        }

        $lastOpen = $this->_editor->article_getLastOpen();
        if ($this->_article_parent !== $lastOpen) {
            $lastOpen->closed = true;
        }

        return $rClass->newInstance($title, $this->_editor, $this->_article_parent);
    }

    public function importArticle(Article $article)
    {
        $article->editor = $this->_editor;
    }

    public function increaseDepth()
    {
        $lastArticle = $this->_editor->article_getLastOpen();
        if ( ! $lastArticle) {
            throw new Exception("Attempted to increase depth with no open articles.");
        }
        $this->_article_parent = $lastArticle;
    }

    public function decreaseDepth()
    {
        $parent = $this->_article_parent;
        if ( ! $parent) {
            throw new Exception("Attempted to decrease depth while already at root.");
        }
        $parent->closeSubArticles();
        $this->_article_parent = $parent->parent;
    }

    public function banner($banner, $sub_title='')
    {
        /** @var $banner Crier\Article\Banner */
        $banner = $this->newArticle('Banner', $banner);
        $banner->sub_title = $sub_title;
        return $banner;
    }

    public function event($title, $status)
    {
        /** @var Event $event */
        $event = $this->newArticle('Event', $title);
        if (is_int($status)) {
            $event->status = $status;
        }
        return $event;
    }

    public function headline($title)
    {
        /** @var $headline Crier\Article\Headline */
        $headline = $this->newArticle('Headline', $title);
        return $headline;
    }

    public function preformatted($title, $content)
    {
        /** @var Preformatted $pre */
        $pre = $this->newArticle('Preformatted', $title);
        $pre->content = $content;
        return $pre;
    }

//    /**
//     * @param $title
//     * @param null $status
//     * @param null $content
//     * @return Crier\Article\Report
//     */
//    public function report($title, $status=null, $content=null)
//    {
//        /** @var $report Article\Report */
//        $report = $this->newArticle('Report', $title);
//        $report->status = $status;
//        $report->content = $content;
//        return $report;
//    }

    public function flushBuffer()
    {
        while (( $buffer = $this->_editor->buffer_read() )) {
            $this->_writer->write($buffer);
        }
    }

    public function _editor_callback($event_id, Editor $editor, $object)
    {
        switch ($event_id) {
            case Editor::ON_ARTICLE_UPDATE:
            case Editor::ON_ARTICLE_CLOSED:
                if ($this->auto_flush_buffer) {
                    $this->flushBuffer();
                }
                break;
        }
    }

    protected function _getDefaultEditor()
    {
        $class = self::$default_editor_class;
        if ( ! class_exists($class)) {
            throw new Exception(
                "Default editor is an unknown class. ($class)",
                Exception::NOT_FOUND
            );
        }

        $editor = new $class();
        if(!($editor instanceof Editor)) {
            throw new Exception(
                "Default editor is an invalid class. ($class)",
                Exception::BAD_VALUE
            );
        }

        return $editor;
    }

    protected function _getDefaultWriter()
    {
        $class = self::$default_writer_class;
        if ( ! class_exists($class)) {
            throw new Exception(
                "Default writer is an unknown class. ($class)",
                Exception::NOT_FOUND
            );
        }

        $writer = new $class();
        if(!($writer instanceof Writer)) {
            throw new Exception(
                "Default writer is an invalid class. ($class)",
                Exception::BAD_VALUE
            );
        }

        return $writer;
    }

}

// END of file
