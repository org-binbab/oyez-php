<?php
namespace OyezTest\CrierTest\ArticleTest;

use Oyez\Crier\Article\Event;
use Oyez\Media\Editor\TextEditor;
use OyezTest\MediaTest\ArticleTest\EditionTest;

class EventTest extends \PHPUnit_Framework_TestCase
{
    public function testTextEdition()
    {
        $editor = new TextEditor(20);

        $tests = array(
            'lvl0, no result' => array(
                'fields' => array(
                ),
                'expect' => "+ ABCDE             \n",
            ),
            'lvl0, ok' => array(
                'fields' => array(
                    'status' => Event::STATUS_SUCCESS
                ),
                'expect' => "\n+ ABCDE       [ OK ]\n",
            ),
            'lvl0, warn' => array(
                'fields' => array(
                    'status' => Event::STATUS_WARNING
                ),
                'expect' => "\n+ ABCDE       [WARN]\n",
                'subart' => array(
                    'lvl1, ok' => array(
                        'fields' => array(
                            'status' => Event::STATUS_SUCCESS
                        ),
                        'expect' => "  - ABCDE     [ OK ]\n",
                    ),
                    'lvl1, warn' => array(
                        'fields' => array(
                            'status' => Event::STATUS_WARNING
                        ),
                        'expect' => "  - ABCDE     [WARN]\n",
                        'subart' => array(
                            'lvl2, fail' => array(
                                'fields' => array(
                                    'status' => Event::STATUS_FAILURE
                                ),
                                'expect' => "    + ABCDE   [FAIL]\n",
                            ),
                        ),
                    ),
                ),
            ),
        );

        EditionTest::runBatchTest(
            $this,
            $editor,
            'Oyez\Crier\Article\Event',
            $tests
        );
    }
}

// END of file
