<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require 'document/xfBuilder.interface.php';
require 'document/xfDocument.class.php';
require 'document/xfField.class.php';
require 'document/xfFieldValue.class.php';

/**
 * A mock builder.
 *
 * @package xfSearch
 * @subpackage Mock
 */
class xfMockBuilder implements xfBuilder
{
  public function build($input, xfDocument $doc)
  {
    $doc->addField(new xfFieldValue(new xfField('foobar', xfField::KEYWORD), 'bar'));
    $doc->addField(new xfFieldValue(new xfField('input', xfField::TEXT), $input));
    
    return $doc;
  }
}
