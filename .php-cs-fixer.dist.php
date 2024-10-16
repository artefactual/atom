<?php

// Exclude cache folder (vendor is already excluded)
// and model classes generated with Propel.
$finder = PhpCsFixer\Finder::create()
    ->exclude('.coverage')
    ->exclude('cache')
    ->notPath('docker/')
    ->notPath('#/model/om/#')
    ->notPath('#/model/map/#')
    ->in(__DIR__)
;

// Indentation inside switch blocks and some multiline
// elements (control statements, assignments, strings)
// is not considered. See:
// https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/776
// https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/4502
//
// PhpCsFixer's method_argument_space default value,
// which includes ensure_fully_multiline, causes
// inconsistencies in templates.
return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@PhpCsFixer' => true,
        'method_argument_space' => ['on_multiline' => 'ignore'],
        'fully_qualified_strict_types' => false,
        'statement_indentation' => false,
        'string_implicit_backslashes' => false,
        'multiline_whitespace_before_semicolons' => false,
        'no_superfluous_phpdoc_tags' => false,
    ])
    ->setFinder($finder)
;
