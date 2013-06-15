<?php
namespace OyezTest\RuntimeTest;

use Oyez\Runtime\ClassWrapper;

class ClassWrapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var ClassWrapper */
    protected $wrapper;

    public function setUp()
    {
        $this->wrapper = new ClassWrapper('OyezTest\RuntimeTest\Sample_Class');
    }

    public function testClassName()
    {
        $this->assertEquals(
            strval($this->wrapper),
            'OyezTest\RuntimeTest\Sample_Class'
        );
        $this->assertEquals(
            $this->wrapper->getClassName(),
            'OyezTest\RuntimeTest\Sample_Class'
        );
        $this->assertEquals(
            $this->wrapper->getClassShortName(),
            'Sample_Class'
        );
    }

    public function testNewInstance()
    {
        /** @var Sample_Class $sample */
        $sample = $this->wrapper->newInstance(array('HELLO WORLD'));
        $this->assertInstanceOf(
            'OyezTest\RuntimeTest\Sample_Class',
            $sample
        );
        $this->assertEquals(
            'HELLO WORLD',
            $sample->aProperty
        );
    }

    public function testGet()
    {
        Sample_Class::reset();
        $this->assertEquals(
            'a static property value',
            $this->wrapper->aStaticProperty
        );
        $this->assertEquals(
            'a constant value',
            $this->wrapper->A_CONSTANT
        );
    }

    public function testSet()
    {
        Sample_Class::reset();
        $this->wrapper->aStaticProperty = 'HELLO WORLD';
        $this->assertEquals(
            'HELLO WORLD',
            Sample_Class::$aStaticProperty
        );
    }

    public function testCall()
    {
        $value = $this->wrapper->{'aStaticMethod'}('HELLO WORLD');
        $this->assertEquals(
            'HELLO WORLD',
            $value
        );
    }
}

// END of file
