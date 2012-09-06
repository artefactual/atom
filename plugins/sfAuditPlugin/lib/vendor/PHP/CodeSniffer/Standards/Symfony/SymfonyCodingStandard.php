<?php

/**
 * Symfony Coding Standard.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Jack Bates <ms419@freezone.co.uk>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   CVS: $Id: SymfonyCodingStandard.php 68 2007-09-21 22:46:08Z jablko $
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 * @link      http://trac.symfony-project.com/trac/wiki/CodingStandards
 */

if (class_exists('PHP_CodeSniffer_Standards_CodingStandard', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_CodingStandard not found');
}

/**
 * Symfony Coding Standard.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Jack Bates <ms419@freezone.co.uk>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 * @link      http://trac.symfony-project.com/trac/wiki/CodingStandards
 */
class PHP_CodeSniffer_Standards_Symfony_SymfonyCodingStandard extends PHP_CodeSniffer_Standards_CodingStandard
{
    /**
     * Return a list of external sniffs to include with this standard.
     *
     * The Symfony standard uses some generic sniffs, and also borrows from the
     * PEAR standard.
     *
     * @return array
     */
    public function getIncludedSniffs()
    {
        return array(

            // Generic sniffs
            'Generic/Sniffs/Classes/DuplicateClassNameSniff.php',
            'Generic/Sniffs/CodeAnalysis/EmptyStatementSniff.php',
            'Generic/Sniffs/CodeAnalysis/ForLoopShouldBeWhileLoopSniff.php',
            'Generic/Sniffs/CodeAnalysis/UnconditionalIfStatementSniff.php',
            'Generic/Sniffs/CodeAnalysis/UnnecessaryFinalModifierSniff.php',
            'Generic/Sniffs/CodeAnalysis/UselessOverridingMethodSniff.php',
            'Generic/Sniffs/Commenting/TodoSniff.php',
            'Generic/Sniffs/ControlStructures/InlineControlStructureSniff.php',
            'Generic/Sniffs/Files/LineEndingsSniff.php',
            'Generic/Sniffs/Formatting/DisallowMultipleStatementsSniff.php',
            'Generic/Sniffs/Formatting/SpaceAfterCastSniff.php',
            'Generic/Sniffs/Functions/OpeningFunctionBraceBsdAllmanSniff.php',
            'Generic/Sniffs/NamingConventions/UpperCaseConstantNameSniff.php',
            'Generic/Sniffs/PHP/DisallowShortOpenTagSniff.php',
            'Generic/Sniffs/PHP/ForbiddenFunctionsSniff.php',
            'Generic/Sniffs/PHP/LowerCaseConstantSniff.php',
            'Generic/Sniffs/Strings/UnnecessaryStringConcatSniff.php',
            'Generic/Sniffs/WhiteSpace/DisallowTabIndentSniff.php',

            // MySource sniffs
            'MySource/Sniffs/Objects/AssignThisSniff.php',
            'MySource/Sniffs/PHP/EvalObjectFactorySniff.php',

            // PEAR sniffs
            'PEAR/Sniffs/Classes/ClassDeclarationSniff.php',
            'PEAR/Sniffs/Commenting/InlineCommentSniff.php',
            'PEAR/Sniffs/Functions/FunctionCallArgumentSpacingSniff.php',
            'PEAR/Sniffs/Functions/ValidDefaultValueSniff.php',

            // Squiz sniffs
            'Squiz/Sniffs/Arrays/ArrayBracketSpacingSniff.php',
            'Squiz/Sniffs/Classes/DuplicatePropertySniff.php',
            'Squiz/Sniffs/Classes/LowercaseClassKeywordsSniff.php',
            'Squiz/Sniffs/ControlStructures/ElseIfDeclarationSniff.php',
            'Squiz/Sniffs/ControlStructures/ForEachLoopDeclarationSniff.php',
            'Squiz/Sniffs/ControlStructures/ForLoopDeclarationSniff.php',
            'Squiz/Sniffs/ControlStructures/LowercaseDeclarationSniff.php',
            'Squiz/Sniffs/Functions/FunctionDeclarationSniff.php',
            'Squiz/Sniffs/Functions/LowercaseFunctionKeywordsSniff.php',
            'Squiz/Sniffs/Objects/ObjectMemberCommaSniff.php',
            'Squiz/Sniffs/Operators/IncrementDecrementUsageSniff.php',
            'Squiz/Sniffs/Operators/ValidLogicalOperatorsSniff.php',
            'Squiz/Sniffs/PHP/LowercasePHPFunctionsSniff.php',
            'Squiz/Sniffs/PHP/NonExecutableCodeSniff.php',

            // Fails to understand symfony multiple line style:
            //
            // protected
            //   $foo,
            //   $bar;
            //
            //'Squiz/Sniffs/Scope/MemberVarScopeSniff.php',

            'Squiz/Sniffs/Scope/MethodScopeSniff.php',
            'Squiz/Sniffs/Scope/StaticThisUsageSniff.php',
            'Squiz/Sniffs/Strings/ConcatenationSpacingSniff.php',
            'Squiz/Sniffs/Strings/DoubleQuoteUsageSniff.php',
            'Squiz/Sniffs/Strings/EchoedStringsSniff.php',
            'Squiz/Sniffs/WhiteSpace/CastSpacingSniff.php',
            'Squiz/Sniffs/WhiteSpace/FunctionOpeningBraceSpaceSniff.php',
            'Squiz/Sniffs/WhiteSpace/LanguageConstructSpacingSniff.php',
            'Squiz/Sniffs/WhiteSpace/ObjectOperatorSpacingSniff.php',
            'Squiz/Sniffs/WhiteSpace/OperatorSpacingSniff.php',
            'Squiz/Sniffs/WhiteSpace/PropertyLabelSpacingSniff.php',
            'Squiz/Sniffs/WhiteSpace/ScopeClosingBraceSniff.php',
            'Squiz/Sniffs/WhiteSpace/SemicolonSpacingSniff.php',
            'Squiz/Sniffs/WhiteSpace/SuperfluousWhitespaceSniff.php',

            // Zend sniffs
            'Zend/Sniffs/Files/ClosingTagSniff.php');
    }
}
