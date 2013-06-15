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

    public function testClassUndefined()
    {
        $main =& $this->script->main;
        $main[] = array('debug_path', 'OyezTest.RuntimeTest.Undefined');
        $this->setExpectedException('Exception', 'class', Exception::NOT_FOUND);
        $this->script->run();
    }

    public function testVariableUndefined()
    {
        $main =& $this->script->main;
        $main[] = array('debug_path', '$undefined');
        $this->setExpectedException('Exception', 'variable', Exception::NOT_FOUND);
        $this->script->run();
    }

    public function testPropertyUndefined()
    {
        $main =& $this->script->main;
        $main[] = array('debug_path', '$this.undefined');
        $this->setExpectedException('Exception', 'property', Exception::NOT_FOUND);
        $this->script->run();
    }

    public function testProperty()
    {
        $main =& $this->script->main;
        $main[] = array('debug_path', '$this.testProperty');
        $run = $this->script->run(
            array('testProperty' => 'HELLO WORLD')
        );
        $this->assertEquals(
            'HELLO WORLD',
            $run->vars['debug']
        );
    }

    public function testPropertySubPath()
    {
        $main =& $this->script->main;
        $main[] = array('debug_subpath', '$this.testProperty');
        $run = $this->script->run(
            array('testProperty' => 'HELLO WORLD')
        );
        $this->assertEquals(
            'HELLO WORLD',
            $run->vars['debug']
        );
    }

    // TODO: Alias could become a separate plugin.

    public function testAliasUndefined()
    {
        $main =& $this->script->main;
        $main[] = array('debug_path', '!Sample_Class');
        $this->setExpectedException('Exception', 'alias', Exception::NOT_FOUND);
        $this->script->run();
    }

    public function testAlias()
    {
        $main =& $this->script->main;
        $main[] = array('use', 'OyezTest.RuntimeTest.Sample_Class');
        $main[] = array('debug_path', '!Sample_Class');
        $run = $this->script->run();
        $this->assertEquals(
            'OyezTest\RuntimeTest\Sample_Class',
            $run->vars['debug']
        );
    }

    public function testAliasGivenName()
    {
        $main =& $this->script->main;
        $main[] = array('use', 'OyezTest.RuntimeTest.Sample_Class', 'Sample');
        $main[] = array('debug_path', '!Sample');
        $run = $this->script->run();
        $this->assertEquals(
            'OyezTest\RuntimeTest\Sample_Class',
            $run->vars['debug']
        );
    }

    public function testPathActionNull()
    {
        $main =& $this->script->main;
        $main[] = array('debug_path_action', 'OyezTest.RuntimeTest.Sample_Class', null);
        $run = $this->script->run();
        $this->assertInstanceOf(
            'Oyez\Runtime\ClassWrapper',
            $run->vars['debug']
        );
    }
}

// END of file
