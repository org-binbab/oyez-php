<?php
/**
 * "Oyez" - The Universal Format Library
 *
 * @author      BinaryBabel OSS (<projects@binarybabel.org>)
 * @link        http://code.binbab.org
 * @copyright   Copyright 2013 sha1(OWNER) = df334a7237f10846a0ca302bd323e35ee1463931
 * @license     GNU Lesser General Public License v3 (see LICENSE and COPYING files)
 */

namespace Oyez\Crier\Article;
 
use Oyez\Media\Article\Edition\LinearEdition;
use Oyez\Media\Editor\TextEditor;

class Event_TextEdition extends LinearEdition
{
    public static $symbol_depth_format = array('+', '-');

    public function getLinearSpec()
    {
        return array(
            'title' => null,
            'status' => Event::STATUS_UNKNOWN,
            'sub_articles' => array(),
        );
    }

    protected function _init()
    {
        if ($this->article->getDepth() > 0) {
            $this->editor_flags |= TextEditor::FLAG_NOSPACE;
        }
    }

    protected function _closed()
    {
    }

    protected function _title_format($field, $value, TextEditor $editor, Event $article)
    {
        $depth = $article->getDepth() % count(static::$symbol_depth_format);
        $listType = static::$symbol_depth_format[$depth];
        $editor->indent_space = '  ';
        $content = $editor->plain->list_item($value, $listType);
        $editor->write_parLeft($content);
        return true;
    }

    protected function _status_format($field, $value, TextEditor $editor, Event $article)
    {
        if ($value >= 0) {
            $content = strtoupper($article->status_human);
            $content = sprintf(
                '[%s]',
                $editor->plain->alignCenter($content, ' ', 4)
            );
            $editor->write_parRight($content);
        } else {
            $editor->write_parRight('');
        }
        return true;
    }

    protected function _sub_articles_format()
    {
        return true;
    }
}

// END of file
