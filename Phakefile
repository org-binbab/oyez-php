<?php

desc("Run unit and integration tests");
task('test', function(){
    passthru('./bin/phpunit --configuration test/unit/phpunit.xml test/unit');
});

desc('test', "Preview coveralls report");
task('coverage', function(){
    passthru('./bin/coveralls --dry-run -v --ansi');
});

desc("Upload coveralls report");
task('coverage-upload', function(){
    if (stripos(phpversion(), '5.4.') === 0) {
        passthru('./bin/coveralls -v');
    } else {
        print "Skipping coverage report due to php version.\n";
    }
});

// END of file
