<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 */

declare(strict_types=1);

if (3 !== $argc) {
    fwrite(
        \STDERR,
        "Usage: php test/coverage/check.php <clover.xml> <minimum>\n",
    );

    exit(2);
}

[$script, $reportPath, $minimum] = $argv;
unset($script);

if (!is_file($reportPath) || !is_numeric($minimum)) {
    fwrite(\STDERR, "Coverage report or minimum is invalid.\n");

    exit(2);
}

$report = simplexml_load_file($reportPath);
$metrics = $report?->project->metrics;

if (null === $metrics) {
    fwrite(\STDERR, "Coverage report does not contain project metrics.\n");

    exit(2);
}

$statements = (int) $metrics['statements'];
$coveredStatements = (int) $metrics['coveredstatements'];

if (0 === $statements) {
    fwrite(\STDERR, "Coverage report contains no executable lines.\n");

    exit(2);
}

$coverage = 100 * $coveredStatements / $statements;
$minimum = (float) $minimum;

printf(
    "Runtime line coverage: %.2f%% (minimum: %.2f%%)\n",
    $coverage,
    $minimum,
);

exit($coverage + \PHP_FLOAT_EPSILON < $minimum ? 1 : 0);
