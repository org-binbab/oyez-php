<?php
/**
 * "Oyez" - The Universal Format Library
 *
 * @author      BinaryBabel OSS (<projects@binarybabel.org>)
 * @link        http://code.binbab.org
 * @copyright   Copyright 2013 sha1(OWNER) = df334a7237f10846a0ca302bd323e35ee1463931
 * @license     GNU Lesser General Public License v3 (see LICENSE and COPYING files)
 */

namespace Oyez\Media\Editor;

use Oyez\Format\Text;
use Oyez\Media\Article;
use Oyez\Media\Editor;
use Oyez\Media\Exception;

/**
 * @property-read int $column_width
 * @property int $line_mode
 * @property int $indent_level
 * @property string $indent_space
 * @property-read Text $plain
 */
class TextEditor extends Editor
{
    public static $default_column_width = 60;
    const MIN_COLUMN_WIDTH = 20;

    const FLAG_NOSPACE = 1;

    const LINE_CUTTING = 0;
    const LINE_MARKING = 1;
    const LINE_KEEPING = 2;

    const INDENT_AUTOMATIC = -1;
    const INDENT_ROOTLEVEL =  0;
    public $indent_enabled = true;
    public $indent_level   = self::INDENT_AUTOMATIC;

    protected $_column_width;
    protected $_line_pending;
    protected $_line_mode = self::LINE_CUTTING;
    protected $_plain;
    protected $_state_values;
    protected $_state_fields = array(
        'indent_level',
        'indent_space'
    );

    public function __construct($column_width=0)
    {
        parent::__construct();
        $column_width = $column_width > 0 ? $column_width : self::$default_column_width;
        $column_width = max(self::MIN_COLUMN_WIDTH, $column_width);
        $this->_column_width = $column_width;
        $this->_line_pending = '';
        $this->_plain = new Text($column_width);
        $this->_article_backupState();
    }

    protected function __get_column_width()
    {
        return $this->_column_width;
    }

    protected function __get_plain()
    {
        return $this->_plain;
    }

    protected function __get_indent_space()
    {
        return $this->plain->indent_space;
    }

    protected function __set_indent_space($value)
    {
        $this->plain->indent_space = $value;
    }

    //
    // ARTICLES
    //////////////////////////////////////////////////////////////////////////////////////

    public function article_updated(Article $article, $field)
    {
        $this->_article_backupState(); //TODO Improve testing.
        parent::article_updated($article, $field);
        $this->_article_restoreState();
    }

    protected function _article_wasImported(Article $article)
    {
        parent::_article_wasImported($article);
        if (
            count($this->articles) > 1
            && ! ($this->article_getEdition($article)->editor_flags & self::FLAG_NOSPACE)
        ) {
            $this->write_newline();
        }
    }

    protected function _article_backupState()
    {
        $this->_state_values = array();
        foreach ($this->_state_fields as $field) {
            $this->_state_values[$field] = $this->$field;
        }
    }

    protected function _article_restoreState()
    {
        foreach ($this->_state_values as $field=>$value) {
            $this->$field = $value;
        }
    }


    // ----------------------------------------------------------------------------------


    //
    // LONG LINES
    //////////////////////////////////////////////////////////////////////////////////////

    protected function __get_line_mode()
    {
        return $this->_line_mode;
    }

    protected function __set_line_mode($mode)
    {
        if (in_array(intval($mode), array(
            self::LINE_CUTTING,
            self::LINE_MARKING,
            self::LINE_KEEPING,
        ))) {
            $this->_line_mode = $mode;
        } else {
            throw new Exception("Attempted to set unknown long line mode.", Exception::NOT_FOUND);
        }
    }

    public function setLongLineCutting()
    {
        $this->_line_mode = self::LINE_CUTTING;
    }

    public function setLongLineMarking()
    {
        $this->_line_mode = self::LINE_MARKING;
    }

    public function setLongLineKeeping()
    {
        $this->_line_mode = self::LINE_KEEPING;
    }

    // ----------------------------------------------------------------------------------


    //
    // WRITING
    //////////////////////////////////////////////////////////////////////////////////////

    const ALIGN_CENTER = Text::ALIGN_CENTER;
    const ALIGN_LEFT = Text::ALIGN_LEFT;
    const ALIGN_RIGHT = Text::ALIGN_RIGHT;

    protected $_write_line_previous;

    public function write($content)
    {
        $content = rtrim($content);
        foreach (explode("\n", $content) as $line) {
            $this->write_line($line);
        }
    }

    public function write_line($content, $align=self::ALIGN_LEFT, $pad_char=' ')
    {
        $this->_write_finishLine();
        $content = $this->_align($content, 0, $align, $pad_char);
        $trimContent = trim($content);
        if ($trimContent == "") {
            $this->write_newline();
            return;
        }
        $this->_buffer->write($content . "\n");
        $this->_write_line_previous = $trimContent;
    }

    public function write_newline()
    {
        $this->_write_finishLine();
        if ($this->_write_line_previous !== "") {
            $this->_buffer->write("\n");
        }
        $this->_write_line_previous = "";
    }

    public function write_split($left_content, $right_content)
    {
        $colWidth = $this->_getIndentedColumnWidth();
        $leftWidth = max(0, $colWidth - mb_strlen($right_content));
        $left_content = $this->_align($left_content, $leftWidth);
        $this->write_parLeft($left_content);
        $this->write_parRight($right_content);
    }

    public function write_parLeft($left_content)
    {
        $this->_forceSingleLine($left_content);
        $this->_applyIndent($left_content);
        $this->_buffer->write($left_content);
        $this->_line_pending .= $left_content;
    }

    public function write_parRight($right_content)
    {
        $this->_forceSingleLine($right_content, false);
        $space = $this->_getLineAvailableSpace();
        $strLn = mb_strlen($right_content);
        if ($strLn > $space || $space <= 0) {
            $this->_write_finishLine();
            $space = 0;
        }
        if ($strLn < $this->_column_width) {
            $right_content = $this->_align($right_content, $space, self::ALIGN_RIGHT);
        }
        $this->_buffer->write($right_content . "\n");
        $this->_write_line_previous = $this->_line_pending . $right_content;
        $this->_line_pending = '';
    }

    protected function _align($content, $max_width=0, $align=self::ALIGN_LEFT, $pad_char=' ')
    {
        if ($align == self::ALIGN_LEFT) {
            $this->_forceSingleLine($content);
            $this->_applyIndent($content);
        } else {
            $this->_forceSingleLine($content, false);
        }
        switch ($this->line_mode) {
            case self::LINE_CUTTING:
                $content = $this->_plain->cut($content, $max_width, false);
                break;
            case self::LINE_MARKING:
                $content = $this->_plain->cut_mark($content, $max_width, false);
                break;
            case self::LINE_KEEPING:
                break;
        }
        return $this->_plain->align($align, $content, $pad_char, $max_width);
    }

    protected function _forceSingleLine(&$content, $trim=true)
    {
        if ($trim) {
            $content = rtrim($content);
        }
        if (stripos($content, "\n") !== false) {
            throw new Exception(
                "Attempted to write single line with multiple newlines.",
                Exception::BAD_VALUE
            );
        }
    }

    protected function _getLineAvailableSpace()
    {
        return max(0, $this->_column_width - mb_strlen($this->_line_pending));
    }

    protected function _write_finishLine()
    {
        if (mb_strlen($this->_line_pending) > 0) {
            $space = $this->_getLineAvailableSpace();
            if ($space > 0) {
                $this->_buffer->write($this->_plain->pad('', $space));
            }
            $this->_buffer->write("\n");
            $this->_write_line_previous = $this->_line_pending;
        }
        $this->_line_pending = '';
    }

    //
    // INDENTING
    //////////////////////////////////////////////////////////////////////////////////////

    protected function _applyIndent(&$content)
    {
        $this->_forceSingleLine($content);
        $indentLevel = $this->_getCurrentIndentLevel();
        if ($indentLevel > 0) {
            $content = $this->plain->indent($content, $indentLevel);
        }
    }

    protected function _getCurrentIndentLevel()
    {
        $indentLevel = 0;
        if ($this->indent_enabled) {
            $indentLevel = $this->indent_level;
            $lastArticle = $this->article_getLastUpdated();
            if ($indentLevel === self::INDENT_AUTOMATIC && $lastArticle) {
                $indentLevel = $lastArticle->getDepth();
            }
            $indentLevel = max(0, $indentLevel);
        }
        return $indentLevel;
    }

    protected function _getIndentedColumnWidth()
    {
        $indentLevel = $this->_getCurrentIndentLevel();
        $indentWidthPerLevel = mb_strlen($this->indent_space);
        return max(0, $this->column_width - ($indentLevel * $indentWidthPerLevel));
    }

    // ----------------------------------------------------------------------------------
}

// END of file
