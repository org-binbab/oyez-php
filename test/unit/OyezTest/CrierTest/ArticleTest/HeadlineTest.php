<?php
namespace OyezTest\CrierTest\ArticleTest;

use Oyez\Media\Editor\TextEditor;
use OyezTest\MediaTest\ArticleTest\EditionTest;

class HeadlineTest extends \PHPUnit_Framework_TestCase
{
    public function testTextEdition()
    {
        $editor = new TextEditor(20);

        $tests = array(
            'lv0, default' => array(
                'fields' => array(
                    'title' => 'Hello World',
                ),
                'expect' => '[ HELLO WORLD ]',
                'txfunc' => 'rtrim',
                'subart' => array(
                    'lv1, default' => array(
                        'fields' => array(
                            'title' => 'Hello World',
                        ),
                        'expect' => "\n--> HELLO WORLD",
                        'txfunc' => 'rtrim',
                        'subart' => array(
                            'lv2, default' => array(
                                'fields' => array(
                                    'title' => 'Hello World',
                                ),
                                'expect' => "\n        HELLO WORLD",
                                'txfunc' => 'rtrim',
                            )
                        ),
                    )
                ),
            )
        );

        EditionTest::runBatchTest(
            $this,
            $editor,
            'Oyez\Crier\Article\Headline',
            $tests
        );
    }
}

// END of file
