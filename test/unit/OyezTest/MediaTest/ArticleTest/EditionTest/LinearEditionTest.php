<?php
namespace OyezTest\MediaTest\ArticleTest;

use Oyez\Media\Article;
use Oyez\Media\Editor;
use OyezTest\Support\Common;

class LinearEditionTest extends \PHPUnit_Framework_TestCase
{
    /** @var Editor */
    protected $editor;

    public function setUp()
    {
        $this->editor = $this->getMockForAbstractClass('Oyez\Media\Editor');
        Common::setProtectedProperty($this->editor, '_type', 'FakeLinear');
    }

    /**
     * Assigning each field in order should produce linear output in the buffer.
     */
    public function testLinearEdition()
    {
        $article = new ArticleFake('Test article', $this->editor);
        $this->assertEquals(
            "title:Test article\n",
            $this->editor->buffer_read()
        );
        $article->fieldA = 'AAA';
        $this->assertEquals(
            "fieldA:AAA\n",
            $this->editor->buffer_read()
        );
        $article->fieldB = 'BBB';
        $this->assertEquals(
            "fieldB:BBB\n",
            $this->editor->buffer_read()
        );
        $article->fieldC = 'CCC';
        $this->assertEquals(
            "fieldC:CCC\n",
            $this->editor->buffer_read()
        );
        $article->closed = true;
        $this->assertEquals(
            "EOF\n",
            $this->editor->buffer_read()
        );
        $this->assertEmpty($this->editor->buffer_read());
    }

    /**
     * An empty linear spec should generate an exception.
     */
    public function testLinearSpecEmpty()
    {
        $article = new ArticleFake('Test article', $this->editor);
        $edition = $this->editor->article_getEdition($article);
        $edition->linear_spec = array();
        $this->setExpectedException('Exception', 'cannot be empty');
        $article->fieldA = 'AAA';
    }

    /**
     * An article field not listed in the linear spec should
     * produce no output in the buffer.
     */
    public function testLinearUndefinedField()
    {
        $article = new ArticleFake('Test article', $this->editor);
        $this->editor->buffer_read();
        $edition = $this->editor->article_getEdition($article);
        unset($edition->linear_spec['fieldA']);
        $article->fieldA = 'AAA';
        $this->assertEmpty($this->editor->buffer_read());
    }

    /**
     * Since all the fields were given defaults, closing the
     * article should render them (in order) to the buffer.
     */
    public function testLinearFieldDefault()
    {
        $article = new ArticleFake('Test article', $this->editor);
        $article->closed = true;
        $this->assertEquals(
            "title:Test article\n"
            . "fieldA:~AAA\n"
            . "fieldB:~BBB\n"
            . "fieldC:~CCC\n"
            . "EOF\n",
            $this->editor->buffer_get()
        );
    }

    /**
     * If we assign a field value towards the end of the linear
     * spec, and there is an intermediate empty field without
     * an assigned default, then any output rendered to the
     * buffer should stop just prior to that empty field.
     */
    public function testLinearPartialFieldDefault()
    {
        $article = new ArticleFake('Test article', $this->editor);
        $edition = $this->editor->article_getEdition($article);
        $edition->linear_spec['fieldB'] = null;
        $article->fieldC = 'CCC';
        $this->assertEquals(
            "title:Test article\n"
            . "fieldA:~AAA\n",
            $this->editor->buffer_get()
        );
    }

    /**
     * Closing the article of a linear edition in which any
     * fields are empty and without an assigned default
     * should throw an exception rather than continuing
     * with an incomplete buffer.
     */
    public function testLinearIncompleteException()
    {
        $article = new ArticleFake('Test article', $this->editor);
        $edition = $this->editor->article_getEdition($article);
        $edition->linear_spec['fieldB'] = null;
        $this->setExpectedException('Exception', 'incomplete article');
        $article->closed = true;
    }
}



// END of file
