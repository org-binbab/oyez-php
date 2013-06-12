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

class Preformatted_TextEdition extends LinearEdition
{
    public function getLinearSpec()
    {
        return array(
            'title' => self::NO_DEFAULT,
            'content' => self::NO_DEFAULT,
        );
    }

    protected function _init()
    {
        $depth = $this->article->getDepth();
        if ($depth > 0) {
            $this->editor_flags |= TextEditor::FLAG_NOSPACE;
        }
    }

    protected function _closed()
    {
    }

    protected function _title_format($field, $value, TextEditor $editor)
    {
        $value = strtoupper($value);
        $editor->write_line(" $value ", TextEditor::ALIGN_CENTER, '<');
        return true;
    }

    protected function _content_format($field, $value, TextEditor $editor, Preformatted $article)
    {
        $editor->indent_level = 0;
        $editor->line_mode = TextEditor::LINE_KEEPING;
        $editor->write($value);
        $editor->write_line(" END ", TextEditor::ALIGN_CENTER, '=');
        return true;
    }
}

// END of file
