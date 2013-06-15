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
 * Oyez Crier - Commonly used articles with simplified interface.
 *
 * Core articles include:
 *  - Banner
 *  - Event
 *  - Headline
 *  - Preformatted
 *
 * @property-read Editor $editor
 * @property-read Writer $writer
 */
class Crier extends Object
{
    /**
     * Default class of Editor to use for Crier if not specified in the constructor.
     * @api
     *
     * @var string
     */
    public static $default_editor_class      = '\Oyez\Media\Editor\TextEditor';

    /**
     * Default class of Writer to use for Crier if not specified in the constructor.
     * @api
     *
     * @var string
     */
    public static $default_writer_class      = '\Oyez\Media\Writer\StandardWriter';

    /**
     * Namespace prefix for new articles whose class is given as a short name.
     * This is the default value, it may also be set individually for each instance.
     * @see $article_namespace
     * @api
     *
     * @var string
     */
    public static $default_article_namespace = '\Oyez\Crier\Article';

    // ----------------------------------------------------------------------------------

    /**
     * Namespace base to use when newArticle() is called with a short class name.
     * @see newArticle()
     * @api
     *
     * @var string
     */
    public $article_namespace;

    /**
     * Automatically send buffer to writer on article updates.
     * Buffer can be flushed manually via flushBuffer().
     * @see flushBuffer()
     * @api
     *
     * @var bool
     */
    public $auto_flush_buffer = true;

    // ----------------------------------------------------------------------------------

    /** @var Article */
    protected $_article_parent;
    /** @var Editor */
    protected $_editor;
    /** @var Writer */
    protected $_writer;

    // ----------------------------------------------------------------------------------

    /**
     * Create new Crier instance.
     *
     * Editor and Writer default to classes specified static configuration.
     * @api
     *
     * @param Editor $editor
     * @param Writer $writer
     */
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

    // ----------------------------------------------------------------------------------


    //
    // GETTERS & SETTERS
    //////////////////////////////////////////////////////////////////////////////////////

    protected function __get_editor()
    {
        return $this->_editor;
    }

    protected function __get_writer()
    {
        return $this->_writer;
    }

    // ----------------------------------------------------------------------------------


    //
    // ARTICLES
    //////////////////////////////////////////////////////////////////////////////////////

    /**
     * Create and return new article of given class name.
     *
     * If a short class is used it will be prefixed with the configured articled namespace.
     * @see Crier::$default_article_namespace
     * @api
     *
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

    /**
     * Banner (Article)
     *
     * A prominent header feature a title and optional sub title.
     * @api
     *
     * @param string $banner
     * @param string $sub_title
     * @return Crier\Article\Banner
     */
    public function Banner($banner, $sub_title='')
    {
        /** @var $banner Crier\Article\Banner */
        $banner = $this->newArticle('Banner', $banner);
        $banner->sub_title = $sub_title;
        return $banner;
    }

    /**
     * Event (Article)
     *
     * A titled item with an associated (predefined) status.
     * Status is required, but can be set null initially and supplied later.
     *
     * Available statuses defined in Event class as constants in the form:
     *     Event::STATUS_*
     *
     * @see Event
     * @api
     *
     * @param string $title
     * @param int|null $status
     * @return Event
     */
    public function Event($title, $status)
    {
        /** @var Event $event */
        $event = $this->newArticle('Event', $title);
        if (is_int($status)) {
            $event->status = $status;
        }
        return $event;
    }

    /**
     * Title (Article)
     *
     * A prominent header featuring only a title.
     * @api
     *
     * @param string $title
     * @return Crier\Article\Headline
     */
    public function Headline($title)
    {
        /** @var $headline Crier\Article\Headline */
        $headline = $this->newArticle('Headline', $title);
        return $headline;
    }

    /**
     * Preformatted (Article)
     *
     * A block of text which will be displayed without modification.
     * Whitespace is preserved.
     * @api
     *
     * @param string $title
     * @param string $content
     * @return Preformatted
     */
    public function Preformatted($title, $content)
    {
        /** @var Preformatted $pre */
        $pre = $this->newArticle('Preformatted', $title);
        $pre->content = $content;
        return $pre;
    }

    // TODO: Add report Article type.
//    public function report($title, $status=null, $content=null)
//    {
//        /** @var $report Article\Report */
//        $report = $this->newArticle('Report', $title);
//        $report->status = $status;
//        $report->content = $content;
//        return $report;
//    }

    // ----------------------------------------------------------------------------------


    //
    // DEPTH MANAGEMENT
    //////////////////////////////////////////////////////////////////////////////////////

    /**
     * Increase semantic depth of new articles.
     *
     * Works by assigning current open article as the parent of any new articles.
     * Can be increased multiple times for deeper nesting.
     * Any increase should have a matching decrease before the Crier ends.
     * Consider using group() function when appropriate.
     * @see decreaseDepth()
     * @see group()
     * @api
     *
     * @throws Crier\Exception
     */
    public function increaseDepth()
    {
        $lastArticle = $this->_editor->article_getLastOpen();
        if ( ! $lastArticle) {
            throw new Exception("Attempted to increase depth with no open articles.");
        }
        $this->_article_parent = $lastArticle;
    }

    /**
     * Decrease semantic depth of new articles.
     * @see increaseDepth()
     * @api
     *
     * @throws Crier\Exception
     */
    public function decreaseDepth()
    {
        $parent = $this->_article_parent;
        if ( ! $parent) {
            throw new Exception("Attempted to decrease depth while already at root.");
        }
        $parent->closeSubArticles();
        $this->_article_parent = $parent->parent;
    }

    /**
     * A convenience function for increasing the depth of its sub-commands.
     *
     * Depth is automatically decreased afterwords. Can be nested.
     * @see increaseDepth()
     * @api
     *
     * @param callable $callable
     * @throws Crier\Exception
     */
    public function group($callable)
    {
        if ( ! is_callable($callable)) {
            throw new Exception("Attempted to create group with invalid content.");
        }
        $this->increaseDepth();
        call_user_func($callable, $this);
        $this->decreaseDepth();
    }


    //
    // UTILITY
    //////////////////////////////////////////////////////////////////////////////////////

    /**
     * Manually flush buffer from Editor to Writer.
     * @see $auto_flush_buffer
     * @api
     */
    public function flushBuffer()
    {
        while (( $buffer = $this->_editor->buffer_read() )) {
            $this->_writer->write($buffer);
        }
    }

    /**
     * Import article by assigning it the active editor.
     * @internal
     *
     * @param Article $article
     */
    public function importArticle(Article $article)
    {
        $article->editor = $this->_editor;
    }

    /**
     * Listener subscribed to Editor for updates.
     * @internal
     *
     * @param $event_id
     * @param Editor $editor
     * @param $object
     */
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

    // ----------------------------------------------------------------------------------

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

    // ----------------------------------------------------------------------------------

}

// END of file
