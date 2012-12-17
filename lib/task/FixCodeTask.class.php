<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

class FixCodeTask extends sfBaseTask
{
  /**
   * @see BaseTask::configure()
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('path', sfCommandArgument::REQUIRED | sfCommandArgument::IS_ARRAY, 'FIXME')));

    $this->name = '';
    $this->briefDescription = 'FIXME';
    $this->detailedDescription = <<<EOF
FIXME
EOF;
  }

  /**
   * @see BaseTask::execute()
   */
  protected function execute($arguments = array(), $options = array())
  {
    foreach ($arguments['path'] as $filePath)
    {
      $fileContents = file_get_contents($filePath);

      // Remove byte order marks
      $fileContents = preg_replace("/\xfe\xff/", '', $fileContents);

      // Use consistent line endings
      $fileContents = preg_replace("/\r\n/", "\n", $fileContents);

      // Remove trailing whitespace from lines
      $fileContents = preg_replace("/[ \t]+\n/", "\n", $fileContents);

      // Remove trailing empty lines from file
      $fileContents = preg_replace("/\n+$/", "\n", $fileContents);

      // Collapse multiple blank lines
      $fileContents = preg_replace("/\n{2,}/", "\n\n", $fileContents);

      // Remove blank lines after open braces
      $fileContents = preg_replace("/(\h*{)\n{2,}/", "\\1\n", $fileContents);

      // Remove blank lines before close braces
      $fileContents = preg_replace("/\n{2,}(\h*})/", "\n\\1", $fileContents);

      // Put open braces on next line
      $fileContents = preg_replace('/(\h*)(\S+(?:\h+\S+)*)\h*{/', "\\1\\2\n\\1{", $fileContents);

      // Put close braces on previous line
      $fileContents = preg_replace('/(\h*)}\h*(\S+\V*)/', "\\1}\n\\1\\2", $fileContents);

      // Use this file's preamble
      $preamble = preg_replace('/(\*\/\s*).*$/s', '\1', file_get_contents(__FILE__));
      $fileContents = preg_replace('/^.*?\*\/\s*/s', $preamble, $fileContents);

      // Fix control signature
      $fileContents = preg_replace('/for *\(/', 'for (', $fileContents);
      $fileContents = preg_replace('/foreach *\(/', 'foreach (', $fileContents);
      $fileContents = preg_replace('/if *\(/', 'if (', $fileContents);

      // Fix elseif
      $fileContents = preg_replace('/elseif/', 'else if', $fileContents);

      // Remove empty argument lists from new statements
      $fileContents = preg_replace('/new (\$?\w+)\(\)/', 'new \1', $fileContents);

      // Use lowercase keywords
      $fileContents = preg_replace('/([^\w])FALSE([^\w])/', '\1false\2', $fileContents);
      $fileContents = preg_replace('/([^\w])TRUE([^\w])/', '\1true\2', $fileContents);
      $fileContents = preg_replace('/([^\w])NULL([^\w])/', '\1null\2', $fileContents);

      if (!file_put_contents($filePath, $fileContents))
      {
        throw new Exception('FIXME');
      }
    }
  }
}
