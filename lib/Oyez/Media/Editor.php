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

use Oyez\Common\Buffer;
use Oyez\Common\Object;
use Oyez\Common\ObjectMap;
use Oyez\Common\Utility;
use Oyez\Media\Article\Edition;

/**
 * @property-read ObjectMap $articles
 * @property-read string $type
 * @property-read bool $supports_moa
 */
abstract class Editor extends Object
{
    const ON_ALL            = 0;
    const ON_ARTICLE_IMPORT = 1;
    const ON_ARTICLE_UPDATE = 2;
    const ON_ARTICLE_CLOSED = 4;

    const SUPPORTS_MOA = false;

    /** @var \Oyez\Common\ObjectMap */
    protected $_articles;
    /** @var array */
    protected $_articles_open;
    /** @var array */
    protected $_articles_updating;
    /** @var \Oyez\Common\Buffer */
    protected $_buffer;
    /** @var array */
    protected $_event_listeners;
    /** @var string */
    protected $_type;

    public function __construct()
    {
        $this->_articles_open = array();
        $this->_articles_updating = array();
        $this->_articles = new ObjectMap();
        $this->_articles->newMap('edition');
        $this->_buffer = new Buffer();
        $this->_event_listeners = array('next_id' => 1);
        $this->_type = str_replace(
            Utility::class_getShortName(__CLASS__),
            '',
            Utility::array_getEndValue(explode('_', Utility::class_getShortName($this)))
        );
    }

    /** @codeCoverageIgnore */
    protected function __get_articles()
    {
        return $this->_articles->getObjects();
    }

    /** @codeCoverageIgnore */
    protected function __get_type()
    {
        return $this->_type;
    }

    /** @codeCoverageIgnore */
    protected function __get_supports_moa()
    {
        return static::SUPPORTS_MOA;
    }


    //
    // ARTICLE
    //////////////////////////////////////////////////////////////////////////////////////

    public function article_import(Article $article)
    {
        if (($prevArticle = $this->article_getLastOpen())) {
            if (true !== $prevArticle->closed && ! $this->supports_moa) {
                if ($article->parent !== $prevArticle) {
                    throw new Exception("Editor does not support operating on multiple open articles.");
                }
            }
        }

        $this->_articles[] = $article;  // throws exception on duplicate
        $this->_article_wasImported($article);

        if ( ! $article->closed) {
            $this->_articles_open[] = $article;
        }

        $this->_event_notify(self::ON_ARTICLE_IMPORT, $article);
    }

    public function article_updated(Article $article, $field)
    {
        if ( ! $this->_articles->hasObject($article)) {
            $this->article_import($article);
        }

        array_push($this->_articles_updating, $article);

        if ('editor' === $field) {
            if ( ! $article->closed) {
                throw new Exception("Attempted to reassign editor on unclosed article.");
            }
            $this->article_getEdition($article)->closed();
            $this->_article_wasClosed($article);
            $this->_event_notify(self::ON_ARTICLE_CLOSED, $article);
            return;
        }

        if ('closed' === $field && $article->closed) {
            $openIndex = array_search($article, $this->_articles_open, true);
            if ($openIndex !== false) {
                array_splice($this->_articles_open, $openIndex, 1);
            }
            $this->article_getEdition($article)->closed();
            $this->_article_wasClosed($article);
            $this->_event_notify(self::ON_ARTICLE_CLOSED, $article);
            return;
        }

        $this->article_getEdition($article)->field($field);
        array_pop($this->_articles_updating);
        $this->_event_notify(self::ON_ARTICLE_UPDATE, $article);
    }

    /**
     * @return Article|null
     */
    public function article_getLastOpen()
    {
        if (count($this->_articles_open) > 0) {
            return end($this->_articles_open);
        }
        return null;
    }

    /**
     * @return Article|null
     */
    public function article_getLastUpdated()
    {
        return end($this->_articles_updating);
    }

    /**
     * @param Article $article
     * @return Edition
     */
    public function article_getEdition(Article $article)
    {
        if ( ! $this->_articles->hasMappedValue($article, 'edition')) {
            $this->_article_newEdition($article);
        }

        return $this->_articles->getMappedValue($article, 'edition');
    }

    public function article_setEdition(Article $article, Edition $edition)
    {
        $this->_articles->setMappedValue($article, 'edition', $edition);
    }

    protected function _article_newEdition(Article $article)
    {
        $this->article_setEdition($article, $article->newEdition());
    }

    protected function _article_wasImported(Article $article)
    {
    }

    protected function _article_wasClosed(Article $article)
    {
    }

    // ----------------------------------------------------------------------------------


    //
    // BUFFER
    //////////////////////////////////////////////////////////////////////////////////////

    public function buffer_read($offset=null)
    {
        return $this->_buffer->read($offset);
    }

    public function buffer_get()
    {
        return $this->_buffer->get();
    }

    // ----------------------------------------------------------------------------------


    //
    // EVENTS
    //////////////////////////////////////////////////////////////////////////////////////

    /**
     * Add a callback which will be invoked on the events indicated by the
     * $event_mask (all events by default). Returns a callback id value
     * that can later be used to remove the callback if desired.
     *
     * Signature of callback: ($event_id, $editor, $source_object)
     *
     * @param callable $callback
     * @param int $event_mask
     * @return int
     */
    public function event_addListener($callback, $event_mask=Editor::ON_ALL)
    {
        $nextId = $this->_event_listeners['next_id']++;
        $this->_event_listeners[$nextId] = array(
            'callback' => $callback,
            'event_mask' => $event_mask
        );
        return $nextId;
    }

    /**
     * Remove a callback, by instance or id returned from event_addListener().
     *
     * @param int|callable $callback
     */
    public function event_delListener($callback)
    {
        if (is_int($callback)) {
            unset($this->_event_listeners[$callback]);
        } else {
            foreach ($this->_event_listeners as $i=>$listener) {
                if ($listener['callback'] === $callback) {
                    unset($this->_event_listeners[$i]);
                }
            }
        }
    }

    protected function _event_notify($event_id, $source_object=null)
    {
        foreach ($this->_event_listeners as $listener) {
            $em = $listener['event_mask'];
            if ($em === 0 || $em & $event_id > 0) {
                call_user_func($listener['callback'], $event_id, $this, $source_object);
            }
        }
    }

    // ----------------------------------------------------------------------------------
}

// END of file
