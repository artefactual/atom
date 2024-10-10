<?php

// Exclude cache and docker folders (vendor is already excluded)
// and model classes generated with Propel.
$finder = PhpCsFixer\Finder::create()
    ->exclude('.coverage')
    ->exclude('cache')
    ->notPath('docker/')
    ->notPath('#/model/om/#')
    ->notPath('#/model/map/#')
    ->in(__DIR__)
;

// method_argument_space, statement_indentation
/*
 * Indentation inside switch blocks and some multiline
 * elements (control statements, assignments, strings)
 * is not considered. See:
 * https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/776
 * https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/4502
 *
 * PhpCsFixer's method_argument_space default value,
 * which includes ensure_fully_multiline, causes
 * inconsistencies in templates.
 */

// fully_qualified_strict_types
/*
 * TODO: Currently the only cases where we don't have fully
 * qualified strict types is where we reference Elastica,
 * and having those helps developers quickly find locations
 * where Elastica is present in the AtoM codebase outside
 * plugins. When ES is no longer deeply integrated with the
 * AtoM code base, this rule exception can be removed.
 */

// string_implicit_backslashes
/*
 * This breaks regex strings, and currently there are no
 * options that distinguish regex strings from regular
 * strings.
 */

// no_superfluous_phpdoc_tags
/*
 * This removes phpdoc where the function signature already
 * includes types. Adding this exception since we still
 * want to retain phpdoc specially if they include a
 * description.
 */

// multiline_whitespace_before_semicolons
/*
 * This prevents semicolons to be moved to a new line in
 * cases where there are chained multiline function calls.
 * Adding this exception since the semicolons in new lines
 * are not indented.
 */
return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@PhpCsFixer' => true,
        'method_argument_space' => ['on_multiline' => 'ignore'],
        'statement_indentation' => false,
        'fully_qualified_strict_types' => false,
        'string_implicit_backslashes' => false,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'no_superfluous_phpdoc_tags' => false,
    ])
    ->setFinder($finder)
;
