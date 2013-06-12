<?php
namespace OyezTest\MediaTest\ArticleTest;

use Oyez\Media\Article;
use Oyez\Media\Article\Edition;
use Oyez\Media\Editor;
use Oyez\Media\Editor\TextEditor;
use Oyez\Media\Exception;

/**
 * @property-read TextEditor $editor
 */
class ArticleFake_TextEdition extends Edition\SimpleEdition
{
    protected function _init()
    {
        $article = $this->article;
        $this->editor_flags = $article->fieldA ?: 0;
    }

    protected function _field($field)
    {
    }

    protected function _closed()
    {
    }

    protected function _render()
    {
        $title = $this->article->title;
        $this->editor->write_line($title);
    }
}

// END of file
