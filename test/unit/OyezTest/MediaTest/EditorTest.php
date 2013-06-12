<?php
namespace OyezTest\MediaTest;

use Oyez\Media\Article;
use Oyez\Media\Article\Edition;
use Oyez\Media\Editor;
use OyezTest\Support\Common;

class EditorTest extends \PHPUnit_Framework_TestCase
{
    /** @var Editor */
    protected $editor;
    protected $callback_counter;

    public function setUp()
    {
        $this->editor = $this->getMockForAbstractClass('Oyez\Media\Editor');
        $this->resetCallbackCounter();
    }

    public function receiveCallback($event_id)
    {
        if ( ! array_key_exists($event_id, $this->callback_counter)) {
            $this->callback_counter[$event_id] = 0;
        }
        $this->callback_counter[$event_id]++;
    }

    public function resetCallbackCounter()
    {
        $this->callback_counter = array();
    }

    /**
     * The editor's type should be derived from its class name.
     */
    public function testType()
    {
        /** @var Editor $editor */
        $editor = $this->getMockBuilder('Oyez\Media\Editor')
            ->setMockClassName(get_class($this->editor) . '_MockEditor')
            ->getMockForAbstractClass();

        $this->assertEquals('Mock', $editor->type);
    }

    /**
     * Missing editions should be created, then cached.
     * The edition's field() method should be invoked following editor updates.
     */
    public function testArticleEdition()
    {
        $article = $this->getMockBuilder('Oyez\Media\Article')
            ->disableOriginalConstructor()
            ->setMethods(array('newEdition'))
            ->getMockForAbstractClass();
        $edition = $this->getMockForAbstractClass('Oyez\Media\Article\Edition', array($article, $this->editor));
        $article
            ->expects($this->once())    // only one edition should be created
            ->method('newEdition')
            ->will($this->returnValue($edition));
        $edition
            ->expects($this->at(0))
            ->method('_field')
            ->with('title');

        /** @var Article $article */
        /** @var Edition $edition */
        $this->editor->article_import($article);
        $this->assertSame(  // the first call should 'create' the edition via newEdition
            $edition,
            $this->editor->article_getEdition($article)
        );
        $this->assertSame(  // call getEdition a second time to ensure value was cached
            $edition,
            $this->editor->article_getEdition($article)
        );

        $article->__construct('Test article', $this->editor);
    }

    /**
     * Multiple unique articles should not be imported if the previous is unclosed.
     */
    public function testMultipleOpen()
    {
        $articleBuilder = $this->getMockBuilder('Oyez\Media\Article')->disableOriginalConstructor();
        /** @var Article $article1 */
        $article1 = $articleBuilder->getMockForAbstractClass();
        /** @var Article $article2 */
        $article2 = $articleBuilder->getMockForAbstractClass();

        $this->editor->article_import($article1);
        $this->setExpectedException('Exception', 'multiple open');
        $this->editor->article_import($article2);
    }

    /**
     * Multiple articles can be imported so long as the previous article was closed.
     */
    public function testSecondArticle()
    {
        $articleBuilder = $this->getMockBuilder('Oyez\Media\Article')->disableOriginalConstructor();
        /** @var Article $article1 */
        $article1 = $articleBuilder->getMockForAbstractClass();
        /** @var Article $article2 */
        $article2 = $articleBuilder->getMockForAbstractClass();

        $this->editor->article_import($article1);
        $article1->closed = true;
        $this->editor->article_import($article2);
    }

    /**
     * A second open article can be imported if it is a sub-article to the first.
     * @depends testMultipleOpen
     */
    public function testSubArticle()
    {
        $articleBuilder = $this->getMockBuilder('Oyez\Media\Article')->disableOriginalConstructor();

        /** @var Article $article1 */
        $article1 = $articleBuilder->getMockForAbstractClass();
        $this->editor->article_import($article1);

        /** @var Article $article2 */
        $article2 = $articleBuilder->getMockForAbstractClass();
        Common::setProtectedProperty($article2, '_parent', $article1);
        $this->editor->article_import($article2);
    }

    /**
     * Assigning the editor after the article is created should throw an Exception
     * if the the article is unclosed.
     */
    public function testReassignUnclosedArticle()
    {
        /** @var Article $article */
        $article = $this->getMockForAbstractClass('Oyez\Media\Article', array('Test article'));
        $this->setExpectedException('Exception', 'unclosed');
        $this->assertNull($article->editor);
        $article->editor = $this->editor;
    }

    /**
     * Closed articles can be assigned new editors.
     * The editor should still call the closed() method on the matching edition.
     */
    public function testReassignClosedArticle()
    {
        $article = $this->getMockForAbstractClass('Oyez\Media\Article', array('Test article'));
        $edition = $this->getMockForAbstractClass('Oyez\Media\Article\Edition', array($article, $this->editor));
        $edition
            ->expects($this->once())
            ->method('_closed');

        /** @var Article $article */
        /** @var Edition $edition */
        $this->editor->article_import($article);
        $this->editor->article_setEdition($article, $edition);
        $article->closed = true;
        $this->assertNull($article->editor);
        $article->editor = $this->editor;
    }

    /**
     * Closing an open article (with assigned editor) should result
     * in an update event for the 'closed' field, followed by the
     * closed() function being called on the edition.
     */
    public function testClosingArticle()
    {
        $article = $this->getMockBuilder('Oyez\Media\Article')
            ->disableOriginalConstructor()                      // wait for edition
            ->getMockForAbstractClass();
        $edition = $this->getMockForAbstractClass('Oyez\Media\Article\Edition', array($article, $this->editor));
        $edition
            ->expects($this->at(1))
            ->method('_field')
            ->with($this->equalTo('closed'));
        $edition
            ->expects($this->once())
            ->method('_closed');

        /** @var Article $article */
        /** @var Edition $edition */
        $this->editor->article_import($article);
        $this->editor->article_setEdition($article, $edition);
        $article->__construct('Test article', $this->editor);   // edition assigned, call constructor
        $article->closed = true;
        $article->editor = $this->editor;
    }

    public function testArticleEvents()
    {
        $cc =& $this->callback_counter;
        $cb = array($this, 'receiveCallback');
        $this->editor->event_addListener($cb);

        $article = $this->getMockBuilder('Oyez\Media\Article')
            ->disableOriginalConstructor()                      // wait for edition
            ->getMockForAbstractClass();
        $edition = $this->getMockForAbstractClass('Oyez\Media\Article\Edition', array($article, $this->editor));
        /** @var Article $article */
        /** @var Edition $edition */

        $this->editor->article_import($article);
        $this->editor->article_setEdition($article, $edition);
        $article->__construct('Test article', $this->editor);   // edition assigned, call constructor
        $article->closed = true;
        $this->assertEquals(1, $cc[Editor::ON_ARTICLE_IMPORT]);
        $this->assertEquals(2, $cc[Editor::ON_ARTICLE_UPDATE]);
        $this->assertEquals(1, $cc[Editor::ON_ARTICLE_CLOSED]);
    }

    public function testEventDel()
    {
        $cc =& $this->callback_counter;
        $cb = array($this, 'receiveCallback');
        $listener1 = $this->editor->event_addListener($cb);
        $listener2 = $this->editor->event_addListener($cb);
        $listener3 = $this->editor->event_addListener($cb);

        /** @var Article $article */
        $article = $this->getMockBuilder('Oyez\Media\Article')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $article->closed = true;

        // Three listeners should generate three import callbacks.
        $this->editor->article_import($article);
        $this->assertEquals(3, $cc[Editor::ON_ARTICLE_IMPORT]);
        $this->resetCallbackCounter();

        // Removing one listener should add two import callbacks.
        $this->editor->event_delListener($listener3);
        $article = $this->getMockBuilder('Oyez\Media\Article')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $article->closed = true;
        $this->editor->article_import($article);
        $this->assertEquals(2, $cc[Editor::ON_ARTICLE_IMPORT]);

        // Removing by callback object should remove remaining two.
        // Zero new callbacks should have been generated.
        $this->editor->event_delListener($cb);
        $article = $this->getMockBuilder('Oyez\Media\Article')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $article->closed = true;
        $this->editor->article_import($article);
        $this->assertEquals(2, $cc[Editor::ON_ARTICLE_IMPORT]);
    }
}

// END of file
