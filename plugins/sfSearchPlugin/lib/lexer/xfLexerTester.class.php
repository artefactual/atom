<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Aids testing a lexer.
 *
 * @package sfSearch
 * @subpackage Lexer
 * @author Carl Vondrick
 */
class xfLexerTester
{
  /**
   * Lime
   *
   * @var lime_test
   */
  private $lime;

  /**
   * The lexer
   *
   * @var xfLexer
   */
  private $lexer;

  /**
   * Constructor to set lime.
   *
   * @param lime_test $lime
   * @param xfLexer $lexer
   */
  public function __construct(lime_test $lime, xfLexer $lexer)
  {
    $this->lime = $lime;
    $this->lexer = $lexer;
  }

  /**
   * Tests that these tokens are created.
   *
   * @param string $input query
   * @param array $expected The tokens expected
   */
  public function pass($input, array $expected, $encoding = 'utf-8')
  {
    $this->lime->diag($input);

    $tokens = $this->lexer->tokenize($input, $encoding);

    if ($this->lime->is(count($tokens), count($expected), count($expected) . ' tokens'))
    {
      for ($x = 0; $x < count($expected); $x++)
      {
        $this->lime->is($tokens[$x]->getLexeme(), $expected[$x][0], 'lexeme is ' . $expected[$x][0]);
        $this->lime->is($tokens[$x]->getType(), $expected[$x][1], 'type is ' . $expected[$x][1]);
      }
    }
    else
    {
      foreach ($tokens as $token)
      {
        $this->lime->diag($token->getLexeme() . ' of type ' . $token->getType());
      }

      $this->lime->skip('', count($expected));
    }
  }

  /**
   * Tests that this input fails
   *
   * @param string $input 
   * @param string $message 
   */
  public function fail($input, $message = null, $encoding = 'utf-8')
  {
    $this->lime->diag($input);

    try 
    {
      $this->lexer->tokenize($input, $encoding);

      $this->lime->fail('exception not thrown');

      if ($message)
      {
        $this->lime->skip('');
      }
    }
    catch (Exception $e)
    {
      $this->lime->pass('exception caught: ' . $e->getMessage());

      if ($message)
      {
        $this->lime->is($e->getMessage(), $message, 'exception message matches');
      }
    }
  }
}
