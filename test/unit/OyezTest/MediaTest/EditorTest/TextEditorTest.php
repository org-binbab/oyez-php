<?php
namespace OyezTest\MediaTest\EditorTest;

use Oyez\Media\Editor\TextEditor;
use Oyez\Media\Exception;
use OyezTest\MediaTest\ArticleTest\ArticleFake;

class TextEditorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TextEditor
     */
    protected $editor;

    public function setUp()
    {
        $this->editor = new TextEditor(20);
    }

    public function testColumnWidth()
    {
        $testWidth = 80;
        $this->assertNotEquals(
            $testWidth,
            TextEditor::$default_column_width
        );
        $editor = new TextEditor($testWidth);
        $this->assertEquals(
            $testWidth,
            $editor->column_width
        );
    }

    public function testColumnWidthDefault()
    {
        $editor = new TextEditor();
        $this->assertEquals(
            TextEditor::$default_column_width,
            $editor->column_width
        );
    }

    public function testColumnWidthMinimum()
    {
        $editor = new TextEditor(1);
        $this->assertEquals(
            TextEditor::MIN_COLUMN_WIDTH,
            $editor->column_width
        );
    }

    public function testLongLineModeDefault()
    {
        $this->assertEquals(
            TextEditor::LINE_CUTTING,
            $this->editor->line_mode
        );

        $this->editor->line_mode = 0;
        $this->assertEquals(
            TextEditor::LINE_CUTTING,
            $this->editor->line_mode
        );
    }

    public function testLongLineModeInvalid()
    {
        $this->setExpectedException('Exception', '', Exception::NOT_FOUND);
        $this->editor->line_mode = -1;
    }

    public function testWriteLine()
    {
        $this->editor->write_line('HELLO*****WORLD*****');
        $this->assertEquals(
            "HELLO*****WORLD*****\n",
            $this->editor->buffer_read()
        );
    }

    public function testWriteAlignment()
    {
        $this->editor->write_line(' LEFT CONTENT ');
        $this->assertEquals(
            " LEFT CONTENT       \n",
            $this->editor->buffer_read()
        );
        $this->editor->write_line(' CENTER CONTENT ', TextEditor::ALIGN_CENTER);
        $this->assertEquals(
            "   CENTER CONTENT   \n",
            $this->editor->buffer_read()
        );
        $this->editor->write_line(' RIGHT CONTENT ', TextEditor::ALIGN_RIGHT);
        $this->assertEquals(
            "      RIGHT CONTENT \n",
            $this->editor->buffer_read()
        );
    }

    public function testWriteLineBreak()
    {
        $this->editor->write_newline();
        $this->assertEquals("\n", $this->editor->buffer_read());
        $this->assertNull($this->editor->buffer_read());
    }

    public function testWriteLineException()
    {
        $this->setExpectedException('Exception', '', Exception::BAD_VALUE);
        $this->editor->write_line("HELLO\nWORLD");
    }

    public function testWriteMultipleBlankLines()
    {
        $this->editor->write(implode("\n", array(
           "ALPHA",
            "",
            "",
            "BRAVO",
            "",
            "CHARLIE",
            "DELTA",
            "",
            ""
        )));
        $this->assertEquals(
            "ALPHA\n\nBRAVO\n\nCHARLIE\nDELTA\n",
            preg_replace('/ *$/m', '', $this->editor->buffer_read())
        );
        $this->editor->write_line("Testing partial left/right.");
        $this->editor->buffer_read();
        $this->editor->write_newline();
        $this->editor->write_parLeft("HELLO");
        $this->editor->write_parRight("WORLD");
        $this->editor->write_newline();
        $this->assertEquals(
            "\nHELLO          WORLD\n\n",
            $this->editor->buffer_read()
        );
        $this->editor->write_line("Testing partial left, without right.");
        $this->editor->buffer_read();
        $this->editor->write_newline();
        $this->editor->write_parLeft("HELLO WORLD");
        $this->editor->write_newline();
        $this->assertEquals(
          "\nHELLO WORLD         \n\n",
            $this->editor->buffer_read()
        );
    }

    public function testWriteCutting()
    {
        $this->editor->setLongLineCutting();
        $this->editor->write(
            "CUT>>SHORT\nCUT>>EXACT**********\nCUT>>LONG>**********+++++"
        );
        $this->assertEquals(
            "CUT>>SHORT          \n".
            "CUT>>EXACT**********\n".
            "CUT>>LONG>**********\n",
            $this->editor->buffer_read()
        );
    }

    public function testWriteMarking()
    {
        $this->editor->setLongLineMarking();
        $this->editor->write(
            "MARK>SHORT\nMARK>EXACT**********\nMARK>LONG>**********+++++"
        );
        $this->assertEquals(
            "MARK>SHORT          \n".
            "MARK>EXACT**********\n".
            "MARK>LONG>*******...\n",
            $this->editor->buffer_read()
        );
    }

    public function testWriteKeeping()
    {
        $this->editor->setLongLineKeeping();
        $this->editor->write(
            "KEEP>SHORT\nKEEP>EXACT**********\nKEEP>LONG>**********+++++"
        );
        $this->assertEquals(
            "KEEP>SHORT          \n".
            "KEEP>EXACT**********\n".
            "KEEP>LONG>**********+++++\n",
            $this->editor->buffer_read()
        );
    }

    public function testWritePartial()
    {
        $this->editor->write_parLeft("HELLO");
        $this->editor->write_parRight("WORLD");
        $this->assertEquals(
            "HELLO          WORLD\n",
            $this->editor->buffer_read(),
            'partial left,right'
        );

        $this->editor->write_parLeft("HE");
        $this->editor->write_parLeft("LLO");
        $this->editor->write_parRight("WORLD");
        $this->assertEquals(
            "HELLO          WORLD\n",
            $this->editor->buffer_read(),
            'partial left,left,right'
        );

        $this->editor->write_parLeft("SING A SONG");
        $this->editor->write_parRight("OF SIXPENCE");
        $this->assertEquals(
            "SING A SONG         \n         OF SIXPENCE\n",
            $this->editor->buffer_read(),
            'partial left,right wrapped'
        );

        $this->editor->write_parLeft("SING A");
        $this->editor->write_parLeft(" SONG");
        $this->editor->write_parRight("OF SIXPENCE");
        $this->assertEquals(
            "SING A SONG         \n         OF SIXPENCE\n",
            $this->editor->buffer_read(),
            'partial left,left,right wrapped'
        );

        $this->editor->write_parLeft("SING A SONG OF SIXPENCE");
        $this->editor->write_parRight("SING A SONG OF SIXPENCE");
        $this->assertEquals(
            "SING A SONG OF SIXPENCE\nSING A SONG OF SIXPENCE\n",
            $this->editor->buffer_read(),
            'partial left,right long'
        );

        $this->editor->write_parLeft("SING A SONG");
        $this->editor->write_line("OF SIXPENCE");
        $this->assertEquals(
            "SING A SONG         \nOF SIXPENCE         \n",
            $this->editor->buffer_read(),
            'partial left, full line'
        );

        $this->editor->write_parRight("SING A SONG");
        $this->editor->write_parRight("OF SIXPENCE");
        $this->assertEquals(
            "         SING A SONG\n         OF SIXPENCE\n",
            $this->editor->buffer_read(),
            'partial right,right'
        );
    }

    public function testWriteSplit()
    {
        $this->editor->write_split("HELLO", "WORLD");
        $this->assertEquals(
            "HELLO          WORLD\n",
            $this->editor->buffer_read()
        );

        $this->editor->write_split("HELLO*****", "WORLD*****");
        $this->assertEquals(
            "HELLO*****WORLD*****\n",
            $this->editor->buffer_read()
        );

        $this->editor->setLongLineCutting();
        $this->editor->write_split("SING A SONG", "[OF SIXPENCE]");
        $this->assertEquals(
            "SING A [OF SIXPENCE]\n",
            $this->editor->buffer_read()
        );

        $this->editor->setLongLineMarking();
        $this->editor->write_split("SING A SONG", "[OF SIXPENCE]");
        $this->assertEquals(
            "SING...[OF SIXPENCE]\n",
            $this->editor->buffer_read()
        );

        $this->editor->setLongLineKeeping();
        $this->editor->write_split("SING A SONG", "[OF SIXPENCE]");
        $this->assertEquals(
            "SING A SONG         \n       [OF SIXPENCE]\n",
            $this->editor->buffer_read()
        );

        $this->editor->setLongLineKeeping();
        $this->editor->write_split("SING A SONG OF SIXPENCE", "SING A SONG OF SIXPENCE");
        $this->assertEquals(
            "SING A SONG OF SIXPENCE\nSING A SONG OF SIXPENCE\n",
            $this->editor->buffer_read()
        );
    }

    public function testArticle()
    {
        $article = new ArticleFake('Test article 1');
        $article->fieldA = 0;
        $this->assertEmpty($this->editor->buffer_read());
        $article->closed = true;
        $article->editor = $this->editor;
        $this->assertEquals(
            "Test article 1",
            rtrim($this->editor->buffer_read())
        );

        // Second article should have a blank line before it.
        $article = new ArticleFake('Test article 2');
        $article->fieldA = 0;
        $article->closed = true;
        $article->editor = $this->editor;
        $this->assertEquals(
            "\nTest article 2",
            rtrim($this->editor->buffer_read())
        );

        // Third article should not have a blank line.
        // Second article should have a blank line before it.
        $article = new ArticleFake('Test article 3');
        $article->fieldA = TextEditor::FLAG_NOSPACE;
        $article->closed = true;
        $article->editor = $this->editor;
        $this->assertEquals(
            "Test article 3",
            rtrim($this->editor->buffer_read())
        );
    }

    public function testArticleIndent()
    {
        $article1 = new ArticleFake('+ Hello', $this->editor);
        $article1->fieldA = 0;
        $article2 = new ArticleFake('- World', $this->editor, $article1);
        $article2->fieldA = 0;
        $article1->closed = true;
        $this->assertEquals(
            "+ Hello             \n\n    - World         \n",
            $this->editor->buffer_read()
        );
    }

    public function testManualIndent()
    {
        $this->editor->indent_level = 1;
        $this->editor->write_line("HELLO");
        $this->assertEquals(
            "    HELLO           \n",
            $this->editor->buffer_read()
        );
        $this->editor->indent_space = '     ';
        $this->editor->write_line("HELLO");
        $this->assertEquals(
            "     HELLO          \n",
            $this->editor->buffer_read()
        );
    }
}

// END of file
