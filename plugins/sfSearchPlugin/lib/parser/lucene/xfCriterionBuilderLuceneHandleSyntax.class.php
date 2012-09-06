<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A builder that handles the Lucene syntax.
 *
 * @package sfSearch
 * @subpackage Parser
 * @author Carl Vondrick
 */
final class xfCriterionBuilderLuceneHandleSyntax extends xfCriterionBuilderActionCommon
{
  /**
   * @see xfFiniteStateMachineAction
   */
  public function execute()
  {
    $current = strtolower($this->builder->getLexeme()->getLexeme());

    switch ($current)
    {
      case '(':
        $this->builder->openBoolean();
        break;
      case ')';
        $this->builder->closeBoolean();
        break;
      case '^':
        $this->builder->addRetroDecorator('xfCriterionBoost', array($this->builder->getLexeme(1)->getLexeme()));
        break;
      case '+':
        $this->builder->addDecorator('xfCriterionRequired');
        break;
      case 'and':
        $this->builder->addRetroDecorator('xfCriterionRequired');
        $this->builder->addDecorator('xfCriterionRequired');
        break;
      case 'or':
        break;
      case '-':
      case '!':
      case 'not':
        $this->builder->addDecorator('xfCriterionProhibited');
        break;
      case '~':
        $last = $this->builder->getLastBoolean()->getLast();

        while ($last instanceof xfCriterionDecorator)
        {
          $last = $last->getCriterion();
        }

        if (!$last instanceof xfCriterionPhrase)
        {
          throw new xfParserException('Attempting to use slop on a ' . get_class($last) . ' query.');
        }

        $last->setSlop($this->builder->getLexeme(1)->getLexeme());
        break;
      case ':';
        // nothing to do for field indicator
        break;
    }
  }
}
