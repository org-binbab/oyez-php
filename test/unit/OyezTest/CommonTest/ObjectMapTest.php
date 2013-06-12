<?php
namespace OyezTest\CommonTest;

use Oyez\Common\Exception;
use Oyez\Common\Object;
use Oyez\Common\ObjectMap;

class ObjectMapTest extends \PHPUnit_Framework_TestCase
{
    protected static $objectA;
    protected static $objectB;

    public static function setUpBeforeClass()
    {
        self::$objectA = new Object();
        self::$objectB = new Object();
    }

    /** @var ObjectMap */
    protected $map;

    public function setUp()
    {
        $this->map = new ObjectMap();
    }

    // ----------------------------------------------------------------------------------

    public function testAddUniqueObject()
    {
        $a = self::$objectA;
        $b = self::$objectB;
        $this->assertFalse($this->map->hasObject($a));
        $this->assertFalse($this->map->hasObject($b));
        $this->map->addObject($a);
        $this->assertTrue($this->map->hasObject($a));
        $this->assertFalse($this->map->hasObject($b));
    }

    public function testAddDuplicateObject()
    {
        $a = self::$objectA;
        $this->setExpectedException('Exception', '', Exception::DUPLICATE);
        $this->map->addObject($a);
        $this->map->addObject($a);
    }

    public function testDelUniqueObject()
    {
        $a = self::$objectA;
        $b = self::$objectB;
        $this->map->addObject($a);
        $this->map->addObject($b);
        $this->assertTrue($this->map->hasObject($a));
        $this->assertTrue($this->map->hasObject($b));
        $this->map->delObject($a);
        $this->assertFalse($this->map->hasObject($a));
        $this->assertTrue($this->map->hasObject($b));
    }

    public function testDelUnknownObject()
    {
        $a = self::$objectA;
        $this->setExpectedException('Exception', '', Exception::NOT_FOUND);
        $this->map->delObject($a);
    }

    public function testGetObjects()
    {
        $a = self::$objectA;
        $b = self::$objectB;
        $this->map->addObject($a);
        $this->map->addObject($b);
        $this->assertEquals(
            array($a, $b),
            $this->map->getObjects()
        );
    }

    public function testGetLastObject()
    {
        $a = self::$objectA;
        $b = self::$objectB;
        $this->map->addObject($a);
        $this->map->addObject($b);
        $this->assertSame(
            $b,
            $this->map->getLastObject()
        );
    }

    // ----------------------------------------------------------------------------------

    public function testNewMap()
    {
        $map1 = 'map1';
        $this->assertFalse($this->map->hasMap($map1));
        $this->map->newMap($map1);
        $this->assertTrue($this->map->hasMap($map1));
    }

    public function testDuplicateMap()
    {
        $map1 = 'map1';
        $this->setExpectedException('Exception', '', Exception::DUPLICATE);
        $this->map->newMap($map1);
        $this->map->newMap($map1);
    }

    public function testDelMap()
    {
        $map1 = 'map1';
        $this->map->newMap($map1);
        $this->assertTrue($this->map->hasMap($map1));
        $this->map->delMap($map1);
        $this->assertFalse($this->map->hasMap($map1));
    }

    public function testDelUnknownMap()
    {
        $map1 = 'map1';
        $this->setExpectedException('Exception', '', Exception::NOT_FOUND);
        $this->map->delMap($map1);
    }

    // ----------------------------------------------------------------------------------

    public function testMapValue()
    {
        $map1 = 'map1';
        $this->map->newMap($map1);
        $a = self::$objectA;
        $this->map->addObject($a);
        $this->assertFalse($this->map->hasMappedValue($a, $map1));
        $this->assertNull($this->map->getMappedValue($a, $map1));
        $this->map->setMappedValue($a, $map1, 'AAAA');
        $this->assertTrue($this->map->hasMappedValue($a, $map1));
        $this->assertEquals(
            'AAAA',
            $this->map->getMappedValue($a, $map1)
        );
    }

    public function testMapUniqueValue()
    {
        $map1 = 'map1';
        $map2 = 'map2';
        $this->map->newMap($map1);
        $this->map->newMap($map2);
        $a = self::$objectA;
        $b = self::$objectB;
        $this->map->addObject($a);
        $this->map->addObject($b);
        $this->map->setMappedValue($a, $map1, 'AAAA');
        $this->map->setMappedValue($a, $map2, 'BBBB');
        $this->map->setMappedValue($b, $map1, 'CCCC');
        $this->map->setMappedValue($b, $map2, 'DDDD');
        $this->assertEquals(
            'AAAA',
            $this->map->getMappedValue($a, $map1)
        );
        $this->assertEquals(
            'BBBB',
            $this->map->getMappedValue($a, $map2)
        );
        $this->assertEquals(
            'CCCC',
            $this->map->getMappedValue($b, $map1)
        );
        $this->assertEquals(
            'DDDD',
            $this->map->getMappedValue($b, $map2)
        );
    }

    public function testDelMappedValue()
    {
        $map1 = 'map1';
        $this->map->newMap($map1);
        $a = self::$objectA;
        $this->map->addObject($a);
        $this->map->setMappedValue($a, $map1, 'AAAA');
        $this->map->delMappedValue($a, $map1);
        $this->assertFalse($this->map->hasMappedValue($a, $map1));
        $this->assertNull($this->map->getMappedValue($a, $map1));
    }

    public function testMapValue_unknownMap()
    {
        $map1 = 'map1';
        $a = self::$objectA;
        $this->setExpectedException('Exception', 'unknown map', Exception::NOT_FOUND);
        $this->map->addObject($a);
        $this->map->setMappedValue($a, $map1, 'AAAA');
    }

    public function testMapValue_unknownObj()
    {
        $map1 = 'map1';
        $a = self::$objectA;
        $this->setExpectedException('Exception', 'unknown obj', Exception::NOT_FOUND);
        $this->map->newMap($map1);
        $this->map->setMappedValue($a, $map1, 'AAAA');
    }

    public function testGetValue_unknownMap()
    {
        $map1 = 'map1';
        $a = self::$objectA;
        $this->setExpectedException('Exception', 'unknown map', Exception::NOT_FOUND);
        $this->map->addObject($a);
        $this->map->getMappedValue($a, $map1);
    }

    public function testGetValue_unknownObj()
    {
        $map1 = 'map1';
        $a = self::$objectA;
        $this->setExpectedException('Exception', 'unknown obj', Exception::NOT_FOUND);
        $this->map->newMap($map1);
        $this->map->getMappedValue($a, $map1);
    }

    public function testObjectMap()
    {
        $map1 = 'map1';
        $map2 = 'map2';
        $map3 = 'map3';
        $this->map->newMap($map1);
        $this->map->newMap($map2);
        $this->map->newMap($map3);
        $a = self::$objectA;
        $this->map->addObject($a);
        $this->map->setMappedValue($a, $map1, 'AAAA');
        $this->map->setMappedValue($a, $map2, 'BBBB');
        $this->assertEquals(
            array(
                'map1' => 'AAAA',
                'map2' => 'BBBB',
                'map3' => null
            ),
            $this->map->getObjectMap($a)
        );
    }

    public function testObjectMap_unknownObj()
    {
        $a = self::$objectA;
        $this->setExpectedException('Exception', 'unknown obj', Exception::NOT_FOUND);
        $this->map->getObjectMap($a);
    }

    // ----------------------------------------------------------------------------------

    public function testCount()
    {
        $a = self::$objectA;
        $this->assertEquals(0, count($this->map));
        $this->map->addObject($a);
        $this->assertEquals(1, count($this->map));
    }

    public function testIterator()
    {
        $a = self::$objectA;
        $b = self::$objectB;
        $this->map->addObject($a);
        $this->map->addObject($b);
        $testObjects = array($a, $b);
        $i = 0;
        foreach ($this->map as $key=>$obj) {
            $this->assertSame($testObjects[$i], $obj);
            $i++;
        }
    }

    public function testArrayGet()
    {
        $map1 = 'map1';
        $this->map->newMap($map1);
        $a = self::$objectA;
        $b = self::$objectB;
        $this->map->addObject($a);
        $this->map->addObject($b);
        $this->map->setMappedValue($a, $map1, 'AAAA');
        $this->assertEquals(
            array('map1' => 'AAAA'),
            $this->map[$a]
        );
    }

    public function testArrayAppend()
    {
        $a = self::$objectA;
        $map = $this->map;
        $map[] = $a;
        $this->assertTrue($this->map->hasObject($a));
    }

    public function testArraySetOffset()
    {
        $a = self::$objectA;
        $this->setExpectedException('Exception');
        $this->map[0] = $a;
    }

    public function testArrayUnset()
    {
        $a = self::$objectA;
        $this->map->addObject($a);
        unset($this->map[$a]);
        $this->assertFalse($this->map->hasObject($a));
    }

    public function testArrayKeyExists()
    {
        $a = self::$objectA;
        $map = $this->map;
        $this->assertFalse(isset($map[$a]));
        $this->map->addObject($a);
        $this->assertTrue(isset($map[$a]));
    }

    // ----------------------------------------------------------------------------------
}

// END of file
