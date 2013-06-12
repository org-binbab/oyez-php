<?php
namespace OyezTest\CommonTest;

use Oyez\Common\Object;
use Oyez\Common\Exception;

class ObjectMock extends Object
{
    public $__field_prefix = '_';

    // fieldA : already set field
    protected $_fieldA = 'HELLO';

    // fieldB : empty field with external test
    protected $_fieldB;
    public function testGetFieldB() { return $this->_fieldB; }

    // fieldC : ghost property
    protected function __get_fieldC()
    {
        return 'SPOOKY';
    }

    // fieldD : get override
    protected $_fieldD;
    protected function __get_fieldD()
    {
        return md5($this->_fieldD);
    }

    // fieldE : set override (readonly)
    protected $_fieldE = 'CANT TOUCH THIS';
    protected function __set_fieldE($val) { }


    // fieldF : set override (sum)
    protected $_fieldF = 0;
    protected function __set_fieldF($val)
    {
        $this->_fieldF += $val;
    }
}
 
class ObjectTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObjectMock */
    protected $obj;

    public function setUp()
    {
        $this->obj = new ObjectMock();
    }

    public function testMissingProperty()
    {
        $this->setExpectedException('Exception', 'get', Exception::NOT_FOUND);
        $this->obj->B;
    }

    public function testReadField()
    {
        $this->assertEquals(
            'HELLO',
            $this->obj->fieldA
        );
    }

    public function testWriteField()
    {
        $this->assertEquals(
            null,
            $this->obj->fieldB,
            'field starts null'
        );
        $this->obj->fieldB = 'WORLD';
        $this->assertEquals(
            'WORLD',
            $this->obj->fieldB,
            'field recalls set value'
        );
        $this->assertEquals(
            'WORLD',
            $this->obj->testGetFieldB(),
            'internal value matches set value'
        );
    }

    public function testGhostProperty()
    {
        $this->assertEquals(
            'SPOOKY',
            $this->obj->fieldC
        );
        $this->setExpectedException('Exception', 'set', Exception::READ_ONLY);
        $this->obj->fieldC = 'BOO!';
    }

    public function testFieldGetOverride()
    {
        $this->assertEquals(
            md5(null),
            $this->obj->fieldD,
            'field starts md5 null'
        );
        $this->obj->fieldD = 'HASH THIS';
        $this->assertEquals(
            md5('HASH THIS'),
            $this->obj->fieldD,
            'field output should be md5 input'
        );
    }

    public function testFieldSetOverrideReadonly()
    {
        $this->assertEquals(
            'CANT TOUCH THIS',
            $this->obj->fieldE,
            'correct initial value'
        );
        $this->obj->fieldE = 'POKE';
        $this->assertEquals(
            'CANT TOUCH THIS',
            $this->obj->fieldE,
            'initial value unchanged'
        );
    }

    public function testFieldSetOverrideSum()
    {
        $this->assertEquals(
            0,
            $this->obj->fieldF,
            'starts at zero'
        );
        $this->obj->fieldF = 2;
        $this->assertEquals(
            2,
            $this->obj->fieldF,
            '2 = 2'
        );
        $this->obj->fieldF = 3;
        $this->assertEquals(
            5,
            $this->obj->fieldF,
            '2 + 3 = 5'
        );
    }

    /**
     * @depends testMissingProperty
     */
    public function testAlternateFieldPrefix()
    {
        $this->assertEquals(
            null,
            $this->obj->fieldB,
            'field starts null'
        );
        $this->obj->fieldB = 'PEANUT BUTTER';
        $this->obj->__field_prefix = '_field';
        $this->assertEquals(
            'PEANUT BUTTER',
            $this->obj->B,
            'field recalls value under alternate prefix'
        );
        $this->obj->B = 'JELLY TIME!';
        $this->assertEquals(
            'JELLY TIME!',
            $this->obj->testGetFieldB(),
            'field value confirmed via internal storage'
        );
    }

    public function testAlternateFieldPrefixMissing()
    {
        $this->obj->fieldB = 'NOW YOU SEE ME';
        $this->obj->__field_prefix = '_field';
        $this->setExpectedException('Exception', 'set', Exception::NOT_FOUND);
        $this->obj->fieldB = 'NOW YOU DONT';
    }
}

// END of file
