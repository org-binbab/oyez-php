<?php
namespace OyezTest\MediaTest\WriterTest;

use Oyez\Media\Exception;
use Oyez\Media\Writer\StandardWriter;
use OyezTest\MediaTest\WriterTest;

class StandardWriterTest extends WriterTest
{
    /**
     * @var StandardWriter
     */
    protected $writer;

    public function setUp()
    {
        $this->writer = new StandardWriter();
    }

    public function testStandardWrite()
    {
        $this->writer->open();
        $this->expectOutputString('HELLO WORLD');
        $this->writer->write('HELLO WORLD');
        $this->writer->close();
    }
}

// END of file
