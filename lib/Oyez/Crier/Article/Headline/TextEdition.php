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

class Headline_TextEdition extends LinearEdition
{
    public function getLinearSpec()
    {
        return array(
            'title' => null
        );
    }

    protected function _init()
    {
    }

    protected function _closed()
    {
    }

    protected function _title_format($field, $value, TextEditor $editor, Headline $article)
    {
        $value = rtrim($value, '.');
        $value = strtoupper($value);
        switch ($article->getDepth()) {
            case 0:
                $value = sprintf('[ %s ]', $value);
                break;
            case 1:
                $editor->indent_level = 0;
                $value = sprintf('--> %s', $value);
                break;
        }
        $editor->write_line($value);
        return true;
    }
}

// END of file
