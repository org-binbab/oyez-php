<?php
namespace OyezTest\MediaTest\ArticleTest;

use Oyez\Media\Article;
use Oyez\Media\Article\Edition;
use Oyez\Media\Editor;

/**
 *
 */
class ArticleFake_FakeEdition extends Edition
{
    protected function _init()
    {
    }

    protected function _field($field)
    {
        $this->_formatHelper_field($field);
    }

    protected function _closed()
    {
    }

    protected function _title_format($field, $value)
    {
        return true;
    }

    protected function _fieldA_format($field, $value)
    {
        $this->article->fieldA = 'AAA';
        return true;    // should lock field
    }

    protected function _fieldB_format($field, $value)
    {
        $this->article->fieldB = 'BBB';
        return false;   // should not lock field
    }

    protected function _fieldC_format($field, $value)
    {
        // lack of bool return should throw exception
    }
}

// END of file
