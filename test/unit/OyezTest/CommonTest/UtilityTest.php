<?php
namespace OyezTest\CommonTest;

use Oyez\Common\Utility;

class UtilityTest extends \PHPUnit_Framework_TestCase
{
    public function testArrayGetValueAt()
    {
        $input = "A B C";
        $this->assertEquals(
            "A",
            Utility::array_getValueAt(explode(' ', $input), 0)
        );
        $this->assertEquals(
            "B",
            Utility::array_getValueAt(explode(' ', $input), 1)
        );
        $this->assertEquals(
            "C",
            Utility::array_getValueAt(explode(' ', $input), 2)
        );
    }

    public function testArrayGetEndValue()
    {
        $input = "A B C";
        $this->assertEquals(
            "C",
            Utility::array_getEndValue(explode(' ', $input))
        );
    }

    public function testClassGetShortName()
    {
        $this->assertEquals(
            'UtilityTest',
            Utility::class_getShortName(__CLASS__),
            'class name'
        );
        $this->assertEquals(
            'UtilityTest',
            Utility::class_getShortName($this),
            'class instance'
        );
        $this->setExpectedException('Exception', 'does not exist');
        Utility::class_getShortName('An\Invalid\ClassName');
    }
}

// END of file
