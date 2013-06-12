<?php
namespace OyezTest\MediaTest\ArticleTest;

use Oyez\Media\Article;

class ArticleFake extends Article
{
    public $notify_editor = true;

    protected $_fieldA;
    protected $_fieldB;
    protected $_fieldC;
    protected $_fieldD;
    protected $_fieldE;

    public function getRequiredFields()
    {
        return array('fieldA');
    }
}

// END of file
