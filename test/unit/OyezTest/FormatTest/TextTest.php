<?php
namespace OyezTest\FormatTest;

use Oyez\Common\Exception;
use Oyez\Format\Text;

class PlainTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_WIDTH = 10;

    /**
     * @var Text
     */
    protected $format;

    public function setUp()
    {
        $this->format = new Text(self::DEFAULT_WIDTH);
    }

    public function testConstructor()
    {
        $this->assertEquals(self::DEFAULT_WIDTH, $this->format->column_width);
    }

    // ----------------------------------------------------------------------------------

    public function testAlignCenter()
    {
        $strEven = '12345678';
        $strOdd  = '1234567';

        $this->assertEquals(
            " $strEven ",
            $this->format->alignCenter($strEven),
            'center, defaults'
        );

        $this->assertEquals(
            "--$strEven--",
            $this->format->alignCenter($strEven, '-', 12),
            'center, even, even'
        );

        $this->assertEquals(
            "--$strOdd---",
            $this->format->alignCenter($strOdd, '-', 12),
            'center, odd, even'
        );

        $this->assertEquals(
            "--$strEven---",
            $this->format->alignCenter($strEven, '-', 13),
            'center, even, odd'
        );

        $this->assertEquals(
            "---$strOdd---",
            $this->format->alignCenter($strOdd, '-', 13),
            'center, odd, odd'
        );

        $this->assertEquals(
            $strEven,
            $this->format->alignCenter($strEven, ' ', 4),
            'center, short'
        );
    }

    public function testAlignLeft()
    {
        $test  = '12345';

        $this->assertEquals(
            "$test     ",
            $this->format->alignLeft($test),
            'left, defaults'
        );

        $this->assertEquals(
            "$test---",
            $this->format->alignLeft($test, '-', 8),
            'left, custom width and char'
        );

        $this->assertEquals(
            $test,
            $this->format->alignLeft($test, ' ', 4),
            'left, short'
        );
    }

    public function testAlignRight()
    {
        $test  = '12345';

        $this->assertEquals(
            "     $test",
            $this->format->alignRight($test),
            'right, defaults'
        );

        $this->assertEquals(
            "---$test",
            $this->format->alignRight($test, '-', 8),
            'right, custom width and char'
        );

        $this->assertEquals(
            $test,
            $this->format->alignRight($test, ' ', 4),
            'right, short'
        );
    }

    // ----------------------------------------------------------------------------------

    public function testCut()
    {
        $exact  = '0123456789';
        $add_4  = '01234567890123';
        $pos_5  = '01234';
        $neg_3  = '0123456';

        $this->assertEquals(
            $exact,
            $this->format->cut($exact),
            'exact length should be returned intact'
        );

        $this->assertEquals(
            $exact,
            $this->format->cut($add_4),
            'long should be truncated'
        );

        $this->assertEquals(
            $pos_5,
            $this->format->cut($exact, 5),
            'hard max of 5'
        );

        $this->assertEquals(
            "$pos_5   ",
            $this->format->cut($pos_5, 8),
            'short should be padded'
        );
        $this->assertEquals(
            $pos_5,
            $this->format->cut($pos_5, 8, false),
            'short should not be padded'
        );

        $this->assertEquals(
            $neg_3,
            $this->format->cut($exact, -3),
            'hard max of -3'
        );

        $this->assertEquals(
            $neg_3,
            $this->format->cut($neg_3, -3),
            'hard max of -3 (already trimmed)'
        );

        $this->assertEquals(
            $neg_3,
            $this->format->cut($add_4, -3),
            'hard max of -3 (long)'
        );

        $this->assertEquals(
            "$pos_5  ",
            $this->format->cut($pos_5, -3),
            'hard max of -3 (short + padding)'
        );

        $this->assertEquals(
            $pos_5,
            $this->format->cut($pos_5, -3, false),
            'hard max of -3 (short w/o padding)'
        );
    }

    public function testCutMark()
    {
        $exact  = '0123456789';
        $add_4  = '01234567890123';
        $pos_5  = '01234';
        $dot_10 = '0123456...';
        $dot_5  = '01...';
        $alt_10 = '012345~~~~';

        $this->assertEquals(
            $exact,
            $this->format->cut_mark($exact),
            'exact length should be returned intact'
        );

        $this->assertEquals(
            "$pos_5     ",
            $this->format->cut_mark($pos_5),
            'short should be returned intact + padding'
        );

        $this->assertEquals(
            $pos_5,
            $this->format->cut_mark($pos_5, 0, false),
            'short should be returned intact w/o padding'
        );

        $this->assertEquals(
            $dot_10,
            $this->format->cut_mark($add_4),
            'long should be cut and marked'
        );

        $this->assertEquals(
            $dot_5,
            $this->format->cut_mark($exact, -5),
            "hard max of -5"
        );

        $this->format->cut_marker = '~~~~';
        $this->assertEquals(
            $alt_10,
            $this->format->cut_mark($add_4),
            'alternate cut marker, ~4'
        );
    }

    public function testPad()
    {
        $exact  = '0123456789';
        $add_4  = '01234567890123';
        $neg_3  = '0123456';

        $this->assertEquals(
            $exact,
            $this->format->pad($exact),
            'exact length should be returned intact'
        );

        $this->assertEquals(
            $add_4,
            $this->format->pad($add_4),
            'long length should be returned intact'
        );

        $this->assertEquals(
            "$neg_3   ",
            $this->format->pad($neg_3),
            'short should be returned with padding'
        );
    }

    public function testIndent()
    {
        $space   = "    ";
        $line    = "Alpha Bravo Charlie Delta";
        $line_1  = "$space$line";
        $line_2  = "$space$space$line";
        $multi   = implode("\n", array($line, " $line", "$space$line", "\t$line", "\t\t$line"));
        $multi_1 = implode("\n", array($line_1, $line_1, $line_1, $line_1, $line_1));

        $this->assertEquals(
            $line_1,
            $this->format->indent($line, 1),
            'indent single line'
        );

        $this->assertEquals(
            $line_1,
            $this->format->indent($line_1, 1),
            'indent already indented single line'
        );

        $this->assertEquals(
            $line_2,
            $this->format->indent($line, 2),
            'double indent'
        );

        $this->assertEquals(
            $multi_1,
            $this->format->indent($multi, 1),
            'multi-line indent'
        );

        $this->assertEquals(
            "$line_1\n$line_1\n",
            $this->format->indent("$line\n$line\n", 1),
            'preserve trailing newline'
        );

        $this->assertEquals(
            "$line_1  \n$line_1\t",
            $this->format->indent("$line  \n$line\t", 1),
            'preserve trailing multi-line whitespace'
        );
    }

    // ----------------------------------------------------------------------------------

    public function testListFormatIndexAlpha()
    {
        $alpha_tests = array(
            0   => '   A',
            1   => '   A',
            2   => '   B',
            26  => '   Z',
            27  => '  AA',
            28  => '  AB',
            52  => '  AZ',
            53  => '  BA',
            79  => '  CA',
            676 => '  YZ',
            677 => '  ZA',
            702 => '  ZZ',
            703 => ' AAA',
            704 => ' AAB',
            729 => ' ABA',
            973 => ' AKK',
        );

        foreach ($alpha_tests as $input=>$output) {
            $this->assertEquals(
                $output,
                $this->format->list_index(Text::LIST_ABC, $input),
                "alphabetic $input"
            );
        }

        $this->assertEquals(
            '   c',
            $this->format->list_index(Text::LIST_ABC + Text::LIST_LOWERCASE, 3),
            "alphabetic 3, lowercase"
        );
    }

    public function testListFormatIndexRoman()
    {
        $roman_tests = array(
            1   => '   I',
            4   => '  IV',
            5   => '   V',
            59  => ' LIX',
            973 => 'CMLXXIII',
        );

        foreach ($roman_tests as $input=>$output) {
            $this->assertEquals(
                $output,
                $this->format->list_index(Text::LIST_IVX, $input),
                "roman $input"
            );
        }

        $this->assertEquals(
            ' iii',
            $this->format->list_index(Text::LIST_IVX + Text::LIST_LOWERCASE, 3),
            "roman 3, lowercase"
        );
    }

    public function testListFormatIndexNumeric()
    {
        $numeric_tests = array(
            0   => '   1',
            1   => '   1',
            2   => '   2',
            10  => '  10',
            100 => ' 100',
            999 => ' 999',
           1000 => ' 999',
           9999 => ' 999'
        );

        foreach ($numeric_tests as $input=>$output) {
            $this->assertEquals(
                $output,
                $this->format->list_index(Text::LIST_NUM, $input),
                "numeric $input"
            );
        }
    }

    public function testListItem()
    {
        $title = 'HELLO WORLD';

        $this->assertEquals(
            "+ HELLO...\n",
            $this->format->list_item($title),
            'default'
        );

        $this->format->list_force_width = false;

        $this->assertEquals(
            "+ $title\n",
            $this->format->list_item($title),
            'default'
        );

        $this->assertEquals(
            "* $title\n",
            $this->format->list_item($title, '*'),
            'alt symbol'
        );

        $this->assertEquals(
            "   1 $title\n",
            $this->format->list_item($title, Text::LIST_NUM, 1),
            'numeric'
        );

        $this->assertEquals(
            "  10 $title\n",
            $this->format->list_item($title, Text::LIST_NUM, 10),
            'alt numeric'
        );

        $this->assertEquals(
            "   1. $title\n",
            $this->format->list_item($title, Text::LIST_NUM, 1, '', '.'),
            'suffix'
        );

        $this->assertEquals(
            "  (10) $title\n",
            $this->format->list_item($title, Text::LIST_NUM, 10, '(', ')'),
            'prefix and suffix'
        );

        $this->assertEquals(
            "   A $title\n",
            $this->format->list_item($title, Text::LIST_ABC, 1),
            'alphabetic'
        );

        $this->assertEquals(
            "   a $title\n",
            $this->format->list_item($title, Text::LIST_ABC + Text::LIST_LOWERCASE, 1),
            'alphabetic, lower'
        );

        $this->assertEquals(
            "  AB $title\n",
            $this->format->list_item($title, Text::LIST_ABC, 28),
            'alt alphabetic'
        );

        $this->assertEquals(
            "   I $title\n",
            $this->format->list_item($title, Text::LIST_IVX, 1),
            'roman'
        );

        $this->assertEquals(
            "   i $title\n",
            $this->format->list_item($title, Text::LIST_IVX + Text::LIST_LOWERCASE, 1),
            'roman, lower'
        );

        $this->assertEquals(
            " III $title\n",
            $this->format->list_item($title, Text::LIST_IVX, 3),
            'alt roman'
        );
    }

    // ----------------------------------------------------------------------------------

    public function testSeparator()
    {
        $this->format->column_width = 5;
        $this->assertEquals(
            "-----\n",
            $this->format->separator('-'),
            'separator (-)'
        );
        $this->assertEquals(
            "+++++\n",
            $this->format->separator('+'),
            'separator (+)'
        );
    }

    // ----------------------------------------------------------------------------------
}

// END of file
