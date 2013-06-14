<?php
namespace OyezTest\RuntimeTest;

use Oyez\Runtime\Script;

class InstructionTest extends \PHPUnit_Framework_TestCase
{
    /** @var Script */
    protected $script;

    public function setUp()
    {
        $this->script = new Script();
    }
    
    public function testNewObject()
    {
        $main =& $this->script->main;
        $main[] = array('new', 'StdClass');
        $run = $this->script->run();
        $this->assertInstanceOf(
            'StdClass',
            $run->vars['object']
        );
        $this->assertSame(
            $run->vars['last'],
            $run->vars['object']
        );
    }

    public function testNewObjectClassSyntax()
    {
        $main =& $this->script->main;
        $main[] = array('new', 'OyezTest.RuntimeTest.Sample_Class');
        $run = $this->script->run();
        $this->assertInstanceOf(
            'OyezTest\RuntimeTest\Sample_Class',
            $run->vars['object']
        );
    }

    public function testCallStaticMethod()
    {
        Sample_Class::reset();
        $main =& $this->script->main;
        $main[] = array('call', 'OyezTest.RuntimeTest.Sample_Class.aStaticMethod');
        $run = $this->script->run();
        $this->assertEquals(
            'a static method value',
            $run->vars['result']
        );
        $this->assertEquals(
            'a static method value',
            $run->vars['last']
        );
    }

    public function testCallMethod()
    {
        $main =& $this->script->main;
        $main[] = array('new', 'OyezTest.RuntimeTest.Sample_Class');
        $main[] = array('call', '$object.aMethod');
        $run = $this->script->run();
        $this->assertEquals(
            'a method value',
            $run->vars['result']
        );
        $this->assertEquals(
            'a method value',
            $run->vars['last']
        );
    }

    public function testGetStaticValue()
    {
        Sample_Class::reset();
        $main =& $this->script->main;
        $main[] = array('get', 'OyezTest.RuntimeTest.Sample_Class.aStaticProperty');
        $run = $this->script->run();
        $this->assertEquals(
            'a static property value',
            $run->vars['value']
        );
        $this->assertEquals(
            'a static property value',
            $run->vars['last']
        );
    }

    public function testGetValue()
    {
        $main =& $this->script->main;
        $main[] = array('new', 'OyezTest.RuntimeTest.Sample_Class');
        $main[] = array('get', '$object.aProperty');
        $run = $this->script->run();
        $this->assertEquals(
            'a property value',
            $run->vars['value']
        );
        $this->assertEquals(
            'a property value',
            $run->vars['last']
        );
    }

    public function testSetStaticProperty()
    {
        Sample_Class::reset();
        $main =& $this->script->main;
        $main[] = array('set', 'OyezTest.RuntimeTest.Sample_Class.aStaticProperty', 'HELLO');
        $run = $this->script->run();
        $this->assertNull($run->vars['value']);
        $this->assertNull($run->vars['last']);
        $main[] = array('get', 'OyezTest.RuntimeTest.Sample_Class.aStaticProperty');
        $run = $this->script->run();
        $this->assertEquals(
            'HELLO',
            $run->vars['value']
        );
    }

    public function testSetProperty()
    {
        $main =& $this->script->main;
        $main[] = array('new', 'OyezTest.RuntimeTest.Sample_Class');
        $main[] = array('set', '$object.aProperty', 'HELLO');
        $run = $this->script->run();
        $this->assertNull($run->vars['value']);
        $main[] = array('get', '$object.aProperty');
        $run = $this->script->run();
        $this->assertEquals(
            'HELLO',
            $run->vars['value']
        );
    }

    public function testPrint()
    {
        $main =& $this->script->main;
        $main[] = array('print', "HELLO WORLD\n");
        $this->script->run();
        $this->expectOutputString("HELLO WORLD\n");
    }
}

// END of file
