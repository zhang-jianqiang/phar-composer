<?php

// explicitly give VERSION via ENV or ask git for current version
$version = getenv('VERSION');
if ($version === false) {
    $version = ltrim(exec('git describe --always --dirty', $_, $code), 'v');
    if ($code !== 0) {
        fwrite(STDERR, 'Error: Unable to get version info from git. Try passing VERSION via ENV' . PHP_EOL);
        exit(1);
    }
}

// use first argument as output file or use "phar-composer-{version}.phar"
$out = isset($argv[1]) ? $argv[1] : ('phar-composer-' . $version . '.phar');

passthru('
rm -rf build && mkdir build &&
cp -r bin/ src/ composer.json composer.lock LICENSE build/ && rm build/bin/build.php &&
sed -i \'s/@git_tag@/' . $version .'/g\' build/src/Clue/PharComposer/App.php &&
composer install -d build/ --no-dev &&

cd build/vendor && rm -rf */*/tests/ */*/src/tests/ */*/docs/ */*/*.md */*/composer.* */*/phpunit.* */*/.gitignore */*/.*.yml */*/*.xml && cd - >/dev/null &&
cd build/vendor/symfony/ && rm -rf */Symfony/Component/*/Tests/ */Symfony/Component/*/*.md */Symfony/Component/*/composer.* */Symfony/Component/*/phpunit.* */Symfony/Component/*/.gitignore && cd ->/dev/null &&
cd build/vendor/guzzle/guzzle && rm -r phar-stub.php phing/ && cd ->/dev/null &&
cd build/vendor/herrera-io/box && rm -r res && cd ->/dev/null &&
cd build/vendor/phine/path && rm sami.php .travis-phpunit.xml && cd ->/dev/null &&
bin/phar-composer build build/ ' . escapeshellarg($out) . ' &&

echo -n "Reported version is: " && php ' . escapeshellarg($out) . ' --version', $code);
exit($code);
