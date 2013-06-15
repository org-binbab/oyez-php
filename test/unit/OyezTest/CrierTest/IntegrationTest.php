<?php
namespace OyezTest\CrierTest;

class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    public function testCrierIntegrationText()
    {
        $integrationDir = __DIR__ . '/../../../integration';
        $md5_file = "$integrationDir/oyez-sample.txt.md5";
        $this->assertFileExists($md5_file);
        $md5_correct = file_get_contents($md5_file);
        
        $script = \Oyez\Runtime\Script::load_fromFile($integrationDir . '/oyez-sample.json');
        $run = $script->run();
        $output = $run->vars['this']->output->buffer->get();
        $md5_actual = md5($output);

        $this->assertEquals($md5_correct, $md5_actual);
    }
}

// END of file
