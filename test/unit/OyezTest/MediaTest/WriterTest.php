<?php
namespace OyezTest\MediaTest;

use Oyez\Media\Writer;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

class WriterTest extends \PHPUnit_Framework_TestCase
{
    /** @var Writer */
    protected $writer;

    public function setUp()
    {
        if ($this->_isDerivedTest()) {
            throw new \Exception("Must override setUp() in extended writer tests.");
        }
        $this->writer = $this->getMockForAbstractClass('Oyez\Media\Writer');
    }

    public function testWriteBeforeOpen()
    {
        if ($this->writer instanceof MockObject) {
            /** @var MockObject $writerStub */
            $writerStub = $this->writer;
            $writerStub
                ->expects($this->any())
                ->method('is_open')
                ->will($this->returnValue(false));
        }
        $this->setExpectedException('Exception');
        $this->writer->write('NAUGHTY, NAUGHTY');
    }

    public function testOpenAndClose()
    {
        if ( ! $this->_isDerivedTest()) {
            return;
        }
        $this->assertEquals(
            false,
            $this->writer->is_open(),
            'writer starts closed'
        );
        $this->writer->open();
        $this->assertEquals(
            true,
            $this->writer->is_open(),
            'writer opened'
        );
        $this->writer->close();
        $this->assertEquals(
            false,
            $this->writer->is_open(),
            'writer closed'
        );
    }

    public function testCloseClosed()
    {
        if ($this->_isDerivedTest()) {
            return;
        }
        /** @var MockObject $writerStub */
        $writerStub = $this->writer;
        $writerStub
            ->expects($this->any())
            ->method('is_open')
            ->will($this->returnValue(false));
        $writerStub
            ->expects($this->never())
            ->method('_close');
        $this->writer->close();
    }

    public function testOpenOpened()
    {
        if ($this->_isDerivedTest()) {
            return;
        }
        /** @var MockObject $writerStub */
        $writerStub = $this->writer;
        $writerStub
            ->expects($this->any())
            ->method('is_open')
            ->will($this->returnValue(true));
        $writerStub
            ->expects($this->never())
            ->method('_open');
        $this->writer->open();
    }

    public function testOpenFailure()
    {
        if ($this->_isDerivedTest()) {
            return;
        }
        /** @var MockObject $writerStub */
        $writerStub = $this->writer;
        $writerStub
            ->expects($this->any())
            ->method('is_open')
            ->will($this->returnValue(false));
        $this->setExpectedException('Exception', 'failed to open');
        $this->writer->open();
    }

    public function testCloseFailure()
    {
        if ($this->_isDerivedTest()) {
            return;
        }
        /** @var MockObject $writerStub */
        $writerStub = $this->writer;
        $writerStub
            ->expects($this->any())
            ->method('is_open')
            ->will($this->returnValue(true));
        $this->setExpectedException('Exception', 'failed to close');
        $this->writer->close();
    }

    protected function _isDerivedTest()
    {
        return (get_class($this) != __CLASS__);
    }
}

// END of file
