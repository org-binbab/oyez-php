<?php
namespace OyezTest\CommonTest;

use Oyez\Common\Buffer;

class BufferTest extends \PHPUnit_Framework_TestCase
{
    /** @var Buffer */
    protected $buffer;

    public function setUp()
    {
        $this->buffer = new Buffer();
    }

    public function testEmpty()
    {
        $this->assertEquals(
            '',
            $this->buffer->get()
        );
        $this->assertNull($this->buffer->read());
    }

    public function testWrite()
    {
        $str1 = "HELLO\n";
        $str2 = "WORLD\n";
        $this->buffer->write($str1);
        $this->assertEquals(
            $str1,
            $this->buffer->get()
        );
        $this->buffer->write($str2);
        $this->assertEquals(
            $str1 . $str2,
            $this->buffer->get()
        );
    }

    public function testRead()
    {
        $str1 = "HELLO\n";
        $str2 = "WORLD\n";
        $this->buffer->write($str1);
        $this->assertEquals(
            $str1,
            $this->buffer->read()
        );
        $this->assertNull($this->buffer->read());
        $this->buffer->write($str2);
        $this->assertEquals(
            $str2,
            $this->buffer->read()
        );
        $this->assertNull($this->buffer->read());
    }

    public function testReadOffset()
    {
        $str1 = "HELLO\n";
        $str2 = "WORLD\n";
        $this->buffer->write($str1);
        $this->assertEquals(
            $str1,
            $this->buffer->read(0)
        );
        $this->assertEquals(
            substr($str1, 2),
            $this->buffer->read(2)
        );
        $this->buffer->write($str2);
        $this->assertEquals(
            $str1 . $str2,
            $this->buffer->read(0)
        );
    }

    public function testClear()
    {
        $str1 = "HELLO\n";
        $str2 = "WORLD\n";
        $this->buffer->write($str1);
        $this->buffer->clear();
        $this->buffer->write($str2);
        $this->assertEquals(
            $str2,
            $this->buffer->get()
        );
    }

    public function testClearRead()
    {
        $str1 = "HELLO\n";
        $str2 = "WORLD\n";
        $this->buffer->write($str1);
        $this->buffer->read();
        $this->buffer->clear();
        $this->buffer->write($str2);
        $this->assertEquals(
            $str2,
            $this->buffer->read()
        );
    }
}

// END of file
