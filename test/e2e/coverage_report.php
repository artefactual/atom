<?php

$atomDir = dirname(dirname(__DIR__));

require_once $atomDir.'/vendor/composer/autoload.php';

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlReport;

// The collecting process (coverage_collect.php) includes
// coverage of non PHP files and doesn't include uncovered
// files in the reports. Configuring a filter in the final
// coverage adds the uncovered files to the report.
$filter = new Filter();
$filter->includeDirectory($atomDir);
$filter->excludeDirectory($atomDir.'/.coverage');
$filter->excludeDirectory($atomDir.'/cache');
$filter->excludeDirectory($atomDir.'/docker');
$filter->excludeDirectory($atomDir.'/test');
$filter->excludeDirectory($atomDir.'/vendor');

$finalCoverage = new CodeCoverage(
    (new Selector())->forLineCoverage($filter),
    $filter
);
$finalCoverage->cacheStaticAnalysis($atomDir.'/.coverage/cache');

$finalData = null;
$finalTests = [];

// Merge PHP reports from the collecting process
foreach (glob($atomDir.'/.coverage/collect/*.php') as $path) {
    $partialCoverage = require_once $path;
    $partialData = $partialCoverage->getData(true);
    $partialTests = $partialCoverage->getTests();

    if (!isset($finalData)) {
        $finalData = $partialData;
    } else {
        $finalData->merge($partialData);
    }

    $finalTests = array_merge($finalTests, $partialTests);
}

if (isset($finalData)) {
    // Remove line coverage from non PHP files
    $cleanedLineCoverage = [];
    foreach ($finalData->lineCoverage() as $file => $lines) {
        if ('.php' === substr($file, -4)) {
            $cleanedLineCoverage[$file] = $lines;
        }
    }

    $finalData->setLineCoverage($cleanedLineCoverage);
    $finalCoverage->setData($finalData);
    $finalCoverage->setTests($finalTests);

    $reportPath = $atomDir.'/.coverage/html';
    $report = new HtmlReport();
    $report->process($finalCoverage, $reportPath);

    echo "Report generated in '{$reportPath}'.".PHP_EOL;
} else {
    echo 'Data not found. Report not generated.'.PHP_EOL;

    exit(1);
}
