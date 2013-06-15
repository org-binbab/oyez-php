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

use Oyez\Media\Article\Edition\SimpleEdition;
use Oyez\Media\Editor\TextEditor;

class Banner_TextEdition extends SimpleEdition
{
    public static $separator_depth_format = array('=', '-');

    protected function _init()
    {
    }

    /** @codeCoverageIgnore */
    protected function _field($field)
    {
    }

    /** @codeCoverageIgnore */
    protected function _closed()
    {
    }

    protected function _render()
    {
        /** @var TextEditor $editor */
        $editor = $this->editor;
        /** @var Banner $article */
        $article = $this->article;
        $depth = min($this->article->getDepth(), count(self::$separator_depth_format));

        $editor->indent_level = 0;
        $editor->write_line($editor->plain->separator(self::$separator_depth_format[$depth]));
        $editor->write_parLeft(' ' . $article->title . ' ');
        if ($article->sub_title) {
            $editor->write_parRight(' ' . $article->sub_title . ' ');
        }
        $editor->write_line($editor->plain->separator(self::$separator_depth_format[$depth]));
        $editor->write_newline();
    }
}

// END of file
