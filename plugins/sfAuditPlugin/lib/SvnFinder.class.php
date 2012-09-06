<?php

/*
 * This file is part of the sfAuditPlugin package.
 * (c) 2007 Jack Bates <ms419@freezone.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class SvnFinder extends sfFinder
{
  /**
   * @see sfFinder
   */
  public function setType($name)
  {
    if (strtolower(substr($name, 0, 3)) == 'dir')
    {
      $this->type = 'dir';
    }
    else if (strtolower($name) == 'any')
    {
      $this->type = 'any';
    }
    else
    {
      $this->type = 'file';
    }

    return $this;
  }

  /**
   * @see sfFinder
   */
  public function in()
  {
    // HACK: If in() is called on a file, the file is included in the results.
    // We should be checking that the file is not discarded, and we should be
    // checking more than the first argument.
    $args = func_get_args();
    if (count($args) > 0 && !is_dir($args[0]))
    {
      return array($args[0]);
    }

    return call_user_func_array(array('parent', 'in'), $args);
  }

  /**
   * @see sfFinder
   */
  protected function search_in($dir, $depth = 0)
  {
    if ($depth > $this->maxdepth)
    {
      return array();
    }

    // The rest of the file contains one record for each directory entry.  Each
    // record contains a number of ordered fields as described below.  The
    // fields are terminated by a line feed (0x0a) character.  Empty fields are
    // represented by just the terminator.  Empty fields that are only followed
    // by empty fields may be omitted from the record.  Records are terminated
    // by a form feed (0x0c) and a cosmetic line feed (0x0a).
    //
    // By matching records which follow a form feed and a line feed (\f\n) we
    // ignore the first record, which is for this directory.
    //
    // (?:[^\f\n]*\n){3} matches the 3 don't care fields between the name field
    // and the schedule field.  (?:[^\f\n]*\n){16} matches the 16 don't care
    // fields between the schedule field and the deleted field.  It is enclosed
    // in (?:)? because empty fields that are only followed by empty fields may
    // be omitted from the record.
    //
    // @see libsvn_wc/README
    //
    // $match[1] is the name field
    // $match[2] is the kind field
    // $match[3] is the schedule field, if present
    // $match[4] is the deleted field, if present
    preg_match_all('/\f\n([^\f\n]*)\n([^\f\n]*)\n(?:(?:[^\f\n]*\n){3}([^\f\n]*)\n(?:(?:[^\f\n]*\n){16}([^\f\n]*)\n)?)?/', file_get_contents("$dir/.svn/entries"), $matches, PREG_SET_ORDER);

    $files = array();
    foreach ($matches as $match)
    {
      if (isset($match[3]) && $match[3] == 'delete')
      {
        continue;
      }

      if (isset($match[4]) && $match[4] == 'deleted')
      {
        continue;
      }

      if (($match[2] == $this->type || $this->type == 'any') && $depth >= $this->mindepth && !$this->is_discarded($dir, $match[1]) && $this->match_names($dir, $match[1]) && $this->size_ok($dir, $match[1]) && $this->exec_ok($dir, $match[1]))
      {
        $files[] = realpath($dir.'/'.$match[1]);
      }

      if ($match[2] == 'dir' && !$this->is_pruned($dir, $match[1]))
      {
        $files = array_merge($files, $this->search_in($dir.'/'.$match[1], $depth + 1));
      }
    }

    return $files;
  }

  /**
   * @see sfFinder
   */
  protected function is_discarded($dir, $entry)
  {
    foreach ($this->discards as $args)
    {
      if (preg_match($args[1], $dir.'/'.$entry))
      {
        return true;
      }
    }

    return false;
  }

  /**
   * @see sfFinder
   */
  protected function is_pruned($dir, $entry)
  {
    foreach ($this->prunes as $args)
    {
      if (preg_match($args[1], $dir.'/'.$entry))
      {
        return true;
      }
    }

    return false;
  }

  /**
   * @see sfFinder
   */
  protected function to_regex($str)
  {
    // Regular expression
    if ($str{0} == '/' && $str{strlen($str) - 1} == '/')
    {
      return $str;
    }

    // Anchor path components using path separator
    if ($str{0} != '/')
    {
      $str = '/'.$str;
    }

    // Strip start anchor to support matching path suffixes
    return '#'.substr(sfGlobToRegex::glob_to_regex($str), 2);
  }
}
