<?php
namespace OyezTest\MediaTest\ArticleTest;

use Oyez\Media\Article;
use Oyez\Media\Article\Edition;
use Oyez\Media\Editor;

class EditionTest extends \PHPUnit_Framework_TestCase
{
    /** @var Edition */
    protected $edition;

    public function setUp()
    {
        $article = $this->getMock(
            __NAMESPACE__ . '\ArticleFake',
            array(),
            array('Article test')
        );
        $editor = $this->getMockForAbstractClass('Oyez\Media\Editor');
        /** @var Article $article */
        /** @var Editor $editor */
        $this->edition = new ArticleFake_FakeEdition($article, $editor);
    }

    // ----------------------------------------------------------------------------------

    public function testFormatFuncWithLock()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $articleMock */
        $articleMock = $this->edition->article;
        $articleMock
            ->expects($this->once())
            ->method('__get')
            ->with($this->equalTo('fieldA'));
        $articleMock
            ->expects($this->once())
            ->method('lockField')
            ->with($this->equalTo('fieldA'));
        $this->edition->field('fieldA');
    }

    public function testFormatFuncWithoutLock()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $articleMock */
        $articleMock = $this->edition->article;
        $articleMock
            ->expects($this->once())
            ->method('__get')
            ->with($this->equalTo('fieldB'));
        $articleMock
            ->expects($this->never())
            ->method('lockField');
        $this->edition->field('fieldB');
    }

    public function testFormatFuncInvalid()
    {
        $this->setExpectedException('Exception', 'true/false');
        $this->edition->field('fieldC');
    }

    public function testFormatFuncMissing()
    {
        $this->setExpectedException('Exception', 'Missing format function');
        $this->edition->field('fieldD');
    }

    public function testFormatFuncClosed()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $articleMock */
        $articleMock = $this->edition->article;
        $articleMock
            ->expects($this->never())
            ->method('__get');
        $this->edition->field('closed');
    }

    // ----------------------------------------------------------------------------------

    public function testDirtyArticle()
    {
        $this->assertTrue($this->edition->isDirty());
        $this->assertFalse($this->edition->isClean());
    }

    public function testCleanArticle()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $articleMock */
        $articleMock = $this->edition->article;
        $articleMock
            ->expects($this->once())
            ->method('__get')
            ->with($this->equalTo('closed'))
            ->will($this->returnValue(true));
        $edition = new ArticleFake_FakeEdition($this->edition->article, $this->edition->editor);
        $this->assertFalse($edition->isDirty());
        $this->assertTrue($edition->isClean());
    }

    // ----------------------------------------------------------------------------------

    /**
     * array(
     *   'TEST NAME' => array(
     *      'fields' => array( 'key' => 'val' ),
     *      'expect' => 'expected buffer result',
     *      'txfunc' => 'trim',
     *      'subart' => array()
     *    )
     * )
     *
     * @param \PHPUnit_Framework_TestCase $test_case
     * @param Editor $editor
     * @param string $article_class
     * @param array $tests_and_results
     * @param \Oyez\Media\Article $parent
     * @internal param $tests
     */
    public static function runBatchTest(
        \PHPUnit_Framework_TestCase $test_case,
        Editor $editor,
        $article_class,
        array $tests_and_results,
        Article $parent=null
    ) {
        $rArticle = new \ReflectionClass($article_class);
        foreach ($tests_and_results as $name=>$test) {
            $fields = @$test['fields'] ?: array();
            $expect = $test['expect'];
            $txFunc = @$test['txfunc'];
            $subArt = @$test['subart'] ?: array();

            $title = @$fields['title'] ?: 'ABCDE';
            unset($fields['title']);

            $editor->buffer_read();

            /** @var Article $article */
            $article = $rArticle->newInstance($title, $editor, $parent);
            foreach ($fields as $key=>$val) {
                $article->$key = $val;
            }

            $output = $editor->buffer_read();

            if (count($subArt) > 0) {
                self::runBatchTest(
                    $test_case,
                    $editor,
                    $article_class,
                    $subArt,
                    $article
                );
            }

            $article->closed = true;
            $output .= $editor->buffer_read();

            if (is_callable($txFunc)) {
                $txOutput = call_user_func($txFunc, $output);
                $output = $txOutput ?: $output;
            }

            $test_case->assertEquals($expect, $output);
        }
    }

    // ----------------------------------------------------------------------------------
}



// END of file
