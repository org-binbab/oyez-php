<?php
namespace OyezTest\CrierTest;

use Oyez\Crier;
use Oyez\Crier\Exception;
use Oyez\Media\Writer\LoopWriter;
use OyezTest\MediaTest\ArticleTest\ArticleFake;

class CrierTest extends \PHPUnit_Framework_TestCase
{
    protected static $crier_defaultEditor;
    protected static $crier_defaultWriter;

    public static function setUpBeforeClass()
    {
        self::$crier_defaultEditor = Crier::$default_editor_class;
        self::$crier_defaultWriter = '\Oyez\Media\Writer\LoopWriter';
    }

    /** @var Crier */
    protected $crier;

    protected function setUp()
    {
        Crier::$default_editor_class = self::$crier_defaultEditor;
        Crier::$default_writer_class = self::$crier_defaultWriter;
        $this->crier = new Crier();
        $this->crier->article_namespace = 'OyezTest\MediaTest\ArticleTest';
    }

    public function testEditor()
    {
        $this->assertInstanceOf('Oyez\Media\Editor', $this->crier->editor);
    }

    public function testWriter()
    {
        $this->assertInstanceOf('Oyez\Media\Writer', $this->crier->writer);
    }

    public function testBufferAutomatic()
    {
        /** @var LoopWriter $writer */
        $writer = $this->crier->writer;
        $writerOutput = $writer->buffer;
        $article = $this->crier->newArticle('ArticleFake', 'Test article');
        $article->fieldA = 0;
        $article->closed = true;
        $this->assertEquals(
            "Test article",
            rtrim($writerOutput->read())
        );
    }

    public function testBufferManual()
    {
        $this->crier->auto_flush_buffer = false;
        /** @var LoopWriter $writer */
        $writer = $this->crier->writer;
        $writerOutput = $writer->buffer;
        $article = $this->crier->newArticle('ArticleFake', 'Test article');
        $article->fieldA = 0;
        $article->closed = true;
        $this->assertEmpty($writerOutput->read());
        $this->crier->flushBuffer();
        $this->assertEquals(
            "Test article",
            rtrim($writerOutput->read())
        );
    }

    public function testBufferDestruct()
    {
        $this->crier->auto_flush_buffer = false;
        /** @var LoopWriter $writer */
        $writer = $this->crier->writer;
        $writerOutput = $writer->buffer;
        $article = $this->crier->newArticle('ArticleFake', 'Test article');
        $article->fieldA = 0;
        $article->closed = true;
        $this->assertEmpty($writerOutput->read());
        $this->crier->__destruct();
        $this->assertEquals(
            "Test article",
            rtrim($writerOutput->read())
        );
    }

    public function testDefaultArticleNamespace()
    {
        $crier = new Crier();
        $this->assertEquals(
            $crier->article_namespace,
            Crier::$default_article_namespace
        );
    }

    public function testNewArticle()
    {
        // Article namespace is modified in setUp().
        $article = $this->crier->newArticle('ArticleFake', 'Test article');
        $this->assertInstanceOf(
            'OyezTest\MediaTest\ArticleTest\ArticleFake',
            $article
        );
    }

    public function testNewArticleUnknown()
    {
        $this->setExpectedException('Exception', 'unknown class', Exception::NOT_FOUND);
        $this->crier->newArticle('AnUnknownClass', 'This article class does not exist');
    }

    public function testNewArticleInvalid()
    {
        $this->setExpectedException('Exception', 'invalid class', Exception::BAD_VALUE);
        $this->crier->newArticle('EditionTest', 'This class is not an article');
    }

    public function testArticleAutoClose()
    {
        $article = $this->crier->newArticle('ArticleFake', 'Test article');
        $article->fieldA = 'AAA';
        $this->assertFalse($article->closed);
        $this->crier->newArticle('ArticleFake', 'Test article');
        $this->assertTrue($article->closed);
    }

    public function testArticleImport()
    {
        $article = new ArticleFake('Test article');
        $article->fieldA = 'AAA';
        $article->closed = true;
        $this->assertNull($article->editor);
        $this->crier->importArticle($article);
        $this->assertInstanceOf(
            'Oyez\Media\Editor',
            $article->editor
        );
    }

    public function testDepth()
    {
        $article1 = $this->crier->newArticle('ArticleFake', 'Test article 1');
        $article1->fieldA = 'AAA';
        $article2 = $this->crier->newArticle('ArticleFake', 'Test article 2');
        $article2->fieldA = 'BBB';
        $this->assertEquals(0, $article2->getDepth());
        $this->crier->increaseDepth();
        $article3 = $this->crier->newArticle('ArticleFake', 'Test article 3');
        $article3->fieldA = 'CCC';
        $this->assertEquals(1, $article3->getDepth());
        $this->crier->decreaseDepth();
        $article4 = $this->crier->newArticle('ArticleFake', 'Test article 4');
        $article4->fieldA = 'DDD';
        $this->assertEquals(0, $article4->getDepth());
    }

    public function testIncreaseDepthClosed()
    {
        $this->setExpectedException('Exception', 'no open article');
        $this->crier->increaseDepth();
    }

    public function testDecreaseDepthAtRoot()
    {
        $this->setExpectedException('Exception', 'already at root');
        $this->crier->decreaseDepth();
    }

    public function testDefaultEditorUnknown()
    {
        $this->setExpectedException('Exception', 'unknown class', Exception::NOT_FOUND);
        Crier::$default_editor_class = 'AnUnkownClass';
        new Crier();
    }

    public function testDefaultEditorInvalid()
    {
        $this->setExpectedException('Exception', 'invalid class', Exception::BAD_VALUE);
        Crier::$default_editor_class = 'StdClass';
        new Crier();
    }

    public function testDefaultWriterUnknown()
    {
        $this->setExpectedException('Exception', 'unknown class', Exception::NOT_FOUND);
        Crier::$default_writer_class = 'AnUnkownClass';
        new Crier();
    }

    public function testDefaultWriterInvalid()
    {
        $this->setExpectedException('Exception', 'invalid class', Exception::BAD_VALUE);
        Crier::$default_writer_class = 'StdClass';
        new Crier();
    }
}

// END of file
