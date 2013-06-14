<?php
namespace OyezTest\RuntimeTest;

use Oyez\Runtime\Exception;
use Oyez\Runtime\Script;

class ScriptTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadFromFile()
    {
        $script = Script::load_fromFile(__DIR__ . '/Sample/Script.json');
        $script->run();
        $this->expectOutputString("HELLO WORLD");
    }

    public function testLoadMissingFile()
    {
        $this->setExpectedException('Exception', '', Exception::NOT_FOUND);
        Script::load_fromFile(__DIR__ . '/Sample/ScriptMissing.json');
    }

    public function testLoadParsingFailure()
    {
        $this->setExpectedException('Exception', '', Exception::BAD_VALUE);
        Script::load_fromFile(__DIR__ . '/Sample/Class.php');
    }
}

// END of file
