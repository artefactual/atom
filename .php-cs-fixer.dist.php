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
        'single_line_empty_body' => false,
        'string_implicit_backslashes' => false,
        'no_extra_blank_lines' => ['tokens' => ['extra']],
        'single_line_comment_spacing' => false,
        'no_multiple_statements_per_line' => false,
        'no_unneeded_control_parentheses' => ['statements' => ['break', 'clone', 'continue', 'echo_print', 'return', 'switch_case', 'yield']],
        'multiline_whitespace_before_semicolons' => false,
        'single_space_around_construct' => false,
        'phpdoc_separation' => false,
        'phpdoc_align' => false,
        'phpdoc_trim' => false,
        'phpdoc_order' => ['order' => ['param', 'throws', 'return']],
        'no_superfluous_phpdoc_tags' => false,
        'nullable_type_declaration_for_default_null_value' => false,
        'no_useless_concat_operator' => false,
        'blank_line_before_statement' => false,
    ])
    ->setFinder($finder)
;
