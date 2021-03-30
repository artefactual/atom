<?php

// Exclude cache folder (vendor is already excluded)
// and model classes generated with Propel.
$finder = PhpCsFixer\Finder::create()
    ->exclude('.coverage')
    ->exclude('cache')
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
return PhpCsFixer\Config::create()
    ->setRules([
        '@PhpCsFixer' => true,
        'method_argument_space' => true,
    ])
    ->setFinder($finder)
;
