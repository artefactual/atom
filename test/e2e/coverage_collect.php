<?php

$atomDir = dirname(dirname(__DIR__));

require_once $atomDir.'/vendor/composer/autoload.php';

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\PHP as PhpReport;

// Use an empty filter and trust PCOV configuration.
// Using the includeDirectory and excludeDirectory
// filter's methods increases execution time. Somehow
// this includes coverage of non PHP files and doesn't
// include uncovered files, both things are fixed in
// the final report generation (coverage_report.php).
$filter = new Filter();

$coverage = new CodeCoverage(
    (new Selector())->forLineCoverage($filter),
    $filter
);
$coverage->cacheStaticAnalysis($atomDir.'/.coverage/cache');
$coverage->start('coverage_collect');

register_shutdown_function(function ($coverage, $atomDir) {
    $coverage->stop();

    (new PhpReport())->process(
        $coverage,
        $atomDir.'/.coverage/collect/'.uniqid().'.php'
    );
}, $coverage, $atomDir);

pcntl_async_signals(true);
pcntl_signal(SIGINT, fn () => exit());
pcntl_signal(SIGQUIT, fn () => exit());
pcntl_signal(SIGTERM, fn () => exit());
