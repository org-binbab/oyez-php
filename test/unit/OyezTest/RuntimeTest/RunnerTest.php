<?php
namespace OyezTest\RuntimeTest;

use Oyez\Runtime\Exception;
use Oyez\Runtime\Runner;
use Oyez\Runtime\Script;

class RunnerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Script */
    protected $script;

    public function setUp()
    {
        $this->script = new Script();
    }

    public function testContextObject()
    {
        $context = new \StdClass();
        $context->hello = 'world';
        $run = new Runner($this->script, $context);
        $this->assertEquals(
            $run->vars['this']->hello,
            'world'
        );
    }

    public function testContextIterable()
    {
        $context = array(
            'hello' => 'world'
        );
        $run = new Runner($this->script, $context);
        $this->assertEquals(
            $run->vars['this']->hello,
            'world'
        );
    }

    public function testUndefinedClass()
    {
        $main =& $this->script->main;
        $main[] = array('debug_path', 'OyezTest.RuntimeTest.Undefined');
        $this->setExpectedException('Exception', 'class', Exception::NOT_FOUND);
        $this->script->run();
    }

    public function testUndefinedVariable()
    {
        $main =& $this->script->main;
        $main[] = array('debug_path', '$undefined');
        $this->setExpectedException('Exception', 'variable', Exception::NOT_FOUND);
        $this->script->run();
    }

    public function testUndefinedProperty()
    {
        $main =& $this->script->main;
        $main[] = array('debug_path', '$this.undefined');
        $this->setExpectedException('Exception', 'property', Exception::NOT_FOUND);
        $this->script->run();
    }

    public function testDefinedProperty()
    {
        $main =& $this->script->main;
        $main[] = array('debug_path', '$this.testProperty');
        $run = $this->script->run_withContext(
            array('testProperty' => 'HELLO WORLD')
        );
        $this->assertEquals(
            'HELLO WORLD',
            $run->vars['debug']
        );
    }
}

// END of file
