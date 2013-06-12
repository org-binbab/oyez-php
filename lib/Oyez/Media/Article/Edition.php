<?php
/**
 * "Oyez" - The Universal Format Library
 *
 * @author      BinaryBabel OSS (<projects@binarybabel.org>)
 * @link        http://code.binbab.org
 * @copyright   Copyright 2013 sha1(OWNER) = df334a7237f10846a0ca302bd323e35ee1463931
 * @license     GNU Lesser General Public License v3 (see LICENSE and COPYING files)
 */

namespace Oyez\Media\Article;
 
use Oyez\Common\Object;
use Oyez\Media\Article;
use Oyez\Media\Editor;
use Oyez\Media\Exception;

/**
 * @property-read Article $article
 * @property-read Editor $editor
 * @property int $editor_flags
 */
abstract class Edition extends Object
{
    protected $__field_prefix = '_';

    protected $_article;
    protected $_editor;
    protected $_editor_flags;
    protected $_dirty;

    public function __construct(Article $article, Editor $editor)
    {
        $this->_article = $article;
        $this->_dirty = (bool)!($article->closed);
        $this->_editor = $editor;
        $this->_editor_flags = 0;
        $this->_init();
    }

    /**
     * Invoked by editor when an article's field is updated.
     * @param string $field
     */
    public function field($field)
    {
        $this->_field($field);
    }

    /**
     * Invoked by editor when an article is closed and also
     * when a closed article is imported into a new editor.
     */
    public function closed()
    {
        $this->_closed();
    }

    /** @codeCoverageIgnore */
    abstract protected function _init();

    /** @codeCoverageIgnore */
    abstract protected function _field($field);

    /** @codeCoverageIgnore */
    abstract protected function _closed();

    /**
     * An edition is clean if its article was closed when constructed.
     * @return bool
     */
    public function isClean()
    {
        return (bool) ! $this->_dirty;
    }

    /**
     * An edition is dirty if its article was unclosed when constructed.
     * @return bool
     */
    public function isDirty()
    {
        return (bool) $this->_dirty;
    }

    /**
     * Format a field by calling a helper function matching _{FIELD}_format();
     * The target function is given (FIELD, VALUE, EDITOR, ARTICLE) and must
     * return true/false, depending on if the field's output has been finalized.
     *
     * @param string $field
     * @param string $fn
     * @throws \Oyez\Media\Exception
     * @return bool
     */
    protected function _formatHelper_field($field, $fn='_%s_format')
    {
        if (in_array($field, array(
            'closed'
        ))) {
            return false;
        }
        $fn = sprintf($fn, $field);
        if ( ! method_exists($this, $fn)) {
            throw new Exception("Missing format function for field. ($field)");
        }
        $value = $this->article->$field;
        $result = $this->$fn($field, $value, $this->editor, $this->article);
        if ( ! is_bool($result)) {
            throw new Exception("Format function for field must return true/false.");
        }
        if (true === $result) {
            $this->article->lockField($field);
        }
        return $result;
    }
}

// END of file
