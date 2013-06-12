<?php
namespace OyezTest\MediaTest;

use Oyez\Crier\Exception;
use Oyez\Media\Editor;
use OyezTest\MediaTest\ArticleTest\ArticleFake;

class ArticleTest extends \PHPUnit_Framework_TestCase
{
    /** @var ArticleFake */
    protected $article;

    public function setUp()
    {
        $this->article = new ArticleFake('Test article');
    }

    public function testClosed()
    {
        $this->article->fieldA = 'AAAA';
        $this->article->closed = true;
        $this->setExpectedException('Exception', 'closed article', Exception::READ_ONLY);
        $this->article->fieldA = 'AAAB';
    }

    public function testClosed2x()
    {
        $this->article->fieldA = 'AAAA';
        $this->article->closed = true;
        $this->article->closed = true;
    }

    public function testLockedField()
    {
        $this->article->lockField('fieldA');
        $this->setExpectedException('Exception', 'locked field', Exception::READ_ONLY);
        $this->article->fieldA = 'AAAA';
    }

    public function testRequiredField()
    {
        $this->setExpectedException('Exception', 'required field', Exception::BAD_VALUE);
        $this->article->closed = true;
    }

    public function testEditionWithoutEditor()
    {
        $this->setExpectedException('Exception', 'editor');
        $this->article->newEdition();
    }

    public function testEdition()
    {
        $editor = $this->getMock('Oyez\Media\Editor');
        $editor->expects($this->once())
            ->method('__get')
            ->with($this->equalTo('type'))
            ->will($this->returnValue('Fake'));
        /** @var Editor $editor */
        $article = new ArticleFake('Test article', $editor);
        $edition = $article->newEdition();
        $this->assertInstanceOf(
            __CLASS__ . '\ArticleFake_FakeEdition',
            $edition
        );
    }

    public function testEditionNotFound()
    {
        $editor = $this->getMock('Oyez\Media\Editor');
        /** @var Editor $editor */
        $article = new ArticleFake('Test article', $editor);
        $this->setExpectedException('Exception', 'ArticleFake_Edition', Exception::NOT_FOUND);
        $article->newEdition();
    }

    public function testEditionInvalid()
    {
        $editor = $this->getMock('Oyez\Media\Editor');
        /** @var Editor $editor */
        $article = new ArticleFake('Test article', $editor);
        $this->setExpectedException('Exception', 'StdClass', Exception::BAD_VALUE);
        $article->newEdition('StdClass');
    }

    public function testSubArticles()
    {
        $this->assertEmpty($this->article->sub_articles);
        $subArticleA = new ArticleFake('Sub article a', null, $this->article);
        $subArticleB = new ArticleFake('Sub article b', null, $this->article);
        $this->assertEquals(
            array($subArticleA, $subArticleB),
            $this->article->sub_articles
        );
    }

    public function testSubArticleEditorNotification()
    {
        $article = $this->getMock(
            __CLASS__ . '\ArticleFake',
            array('_notifyEditor'),
            array('Test article')
        );
        $article->expects($this->once())
            ->method('_notifyEditor')
            ->with('sub_articles');
        new ArticleFake('Sub article', null, $article);
    }

    public function testAddSubArticleToClosed()
    {
        $this->article->fieldA = 'AAA';
        $this->article->closed = true;
        $this->setExpectedException('Exception', 'closed');
        new ArticleFake('Sub article a', null, $this->article);
    }

    public function testCloseWithClosedSubArticles()
    {
        $subArticleA = new ArticleFake('Sub article a', null, $this->article);
        $subArticleA->fieldA = 'Aa';
        $subArticleA->closed = true;
        $subArticleB = new ArticleFake('Sub article b', null, $this->article);
        $subArticleB->fieldA = 'Ab';
        $subArticleB->closed = true;
        $this->article->fieldA = 'AAA';
        $this->article->closed = true;
    }

    public function testCloseWithOpenSubArticles()
    {
        $this->article->fieldA = 'AAA';
        $subArticleA = new ArticleFake('Sub article a', null, $this->article);
        $subArticleA->fieldA = 'Aa';
        $subArticleB = new ArticleFake('Sub article b', null, $this->article);
        $subArticleB->fieldA = 'Ab';
        $this->article->closed = true;
    }

    public function testCloseWithOpenSubArticlesFailure()
    {
        $this->article->fieldA = 'AAA';
        $subArticleA = new ArticleFake('Sub article a', null, $this->article);
        $subArticleA->fieldA = 'Aa';
        $subArticleA->closed = true;
        $subArticleB = new ArticleFake('Sub article b', null, $this->article);
        $this->setExpectedException('Exception', 'open sub-articles');
        $this->article->closed = true;
    }

    public function testDepth()
    {
        $this->assertEquals(0, $this->article->getDepth());
        $subArticleA = new ArticleFake('Sub article a', null, $this->article);
        $this->assertEquals(1, $subArticleA->getDepth());
    }

    public function testNotifyGeneral()
    {
        $article = $this->getMock(
            __CLASS__ . '\ArticleFake',
            array('_notifyEditor'),
            array('Test article')
        );
        $article->expects($this->once())
            ->method('_notifyEditor')
            ->with('title');
        /** @var ArticleFake $article */
        $article->title = 'A new title';
    }

    public function testNotifyEditorMock()
    {
        $editor = $this->getMock('Oyez\Media\Editor');
        $editor->expects($this->at(0))
            ->method('article_updated')
            ->with($this->anything(), $this->equalTo('title'));
        $editor->expects($this->at(1))
            ->method('article_updated')
            ->with($this->anything(), $this->equalTo('editor'));
        /** @var Editor $editor */
        $article1 = new ArticleFake('Test article', $editor);
        $article2 = new ArticleFake('Test article');
        $article2->fieldA = 'AAA';
        $article2->closed = true;
        $article2->editor = $editor;
    }

    public function testNotifyEditorDisabled()
    {
        $editor = $this->getMock('Oyez\Media\Editor');
        $editor->expects($this->never())
            ->method('article_updated');
        /** @var Editor $editor */
        $article = new ArticleFake('Test article');
        $article->notify_editor = false;
        $article->fieldA = 'AAA';
        $article->closed = true;
        $article->editor = $editor;
    }
}

// END of file
