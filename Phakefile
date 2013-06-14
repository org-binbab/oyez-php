<?php

$PHP_BIN = defined('PHP_BINARY') ? constant('PHP_BINARY') . ' ' : '';

desc("Run unit and integration tests");
task('test', function () use ($PHP_BIN) {
    passthru($PHP_BIN . './bin/phpunit --configuration test/unit/phpunit.xml test/unit');
});

desc("Preview coveralls report");
task('coverage', 'test', function () use ($PHP_BIN) {
    passthru($PHP_BIN . './bin/coveralls --dry-run -v --ansi');
});

desc("Upload coveralls report");
task('coverage-upload', function () {
    if (stripos(phpversion(), '5.4.') === 0) {
        passthru('./bin/coveralls -v');
    } else {
        print "Skipping coverage report due to php version.\n";
    }
});

// END of file
