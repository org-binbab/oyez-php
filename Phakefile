<?php

require_once(__DIR__ . '/vendor/autoload.php');

function php_bin() {
    return defined('PHP_BINARY') ? constant('PHP_BINARY') . ' ' : '';
}

desc("Run unit and integration tests");
task('test', function () {
    passthru(\php_bin() . './bin/phpunit --configuration test/unit/phpunit.xml test/unit');
});

desc("Preview coveralls report");
task('coverage', 'test', function () {
    passthru(\php_bin() . './bin/coveralls --dry-run -v --ansi');
});

desc("Upload coveralls report");
task('coverage-upload', function () {
    if (stripos(phpversion(), '5.4.') === 0) {
        passthru('./bin/coveralls -v');
    } else {
        print "Skipping coverage report due to php version.\n";
    }
});

group('build', function () {
    desc("Build text output sample");
    task('text-sample', function () {
        $script = \Oyez\Runtime\Script::load_fromFile(__DIR__ . '/test/integration/oyez-sample.json');
        $run = $script->run();
        $output = $run->vars['this']->output->buffer->get();
        file_put_contents(__DIR__ . '/build/oyez-sample.txt', $output);
        file_put_contents(__DIR__ . '/build/oyez-sample.txt.md5', md5($output));
        print $output;
    });
});

// END of file
