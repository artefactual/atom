<?php

/*
 * This file is part of the sfAuditPlugin package.
 * (c) 2007 Jack Bates <ms419@freezone.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class AuditTask extends sfBaseTask
{
  protected
    $patternConfigs = null;

  public static function globToPattern($glob)
  {
    $pattern = '';

    // PREG_SPLIT_NO_EMPTY is a possibly unnecessary optimization
    foreach (preg_split('/(\*|\/\/|\?)/', $glob, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE) as $token)
    {
      switch ($token)
      {
        case '*':
          $pattern .= '[^/]*';
          break;

        case '//':
          $pattern .= '/(?:.*/)?';
          break;

        case '?':
          $pattern .= '[^/]';
          break;

        default:
          $pattern .= preg_quote($token);
      }
    }

    return $pattern;
  }

  /**
   * @see sfTask
   */
  public function initialize(sfEventDispatcher $dispatcher, sfFormatter $formatter)
  {
    parent::initialize($dispatcher, $formatter);

    $globConfigs = sfYaml::load(dirname(__FILE__).'/../../config/audit.yml');

    $this->patternConfigs = array();
    foreach ($globConfigs as $glob => $globConfig)
    {
      // Globs like //foo/... must be matched against paths with a leading
      // slash, while globs like foo/... must be matched against paths without
      // a leading slash.  Consequently, prefix all globs with slash, if
      // necessary, and always match against paths with a leading slash.
      if (strncmp($glob, '/', 1) != 0)
      {
        $glob = '/'.$glob;
      }

      $pattern = self::globToPattern($glob);
      $this->patternConfigs[$pattern] = $globConfig;
    }
  }

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('path', sfCommandArgument::OPTIONAL, 'Filesystem path to the file or directory to audit', sfConfig::get('sf_root_dir'))));

    $this->name = '';
    $this->briefDescription = 'FIXME';
    $this->detailedDescription = <<<EOF
FIXME
EOF;
  }

  protected function getConfigForPath($path)
  {
    if (strncmp($path, sfConfig::get('sf_root_dir'), $len = strlen(sfConfig::get('sf_root_dir'))) == 0)
    {
      $path = substr($path, $len);
    }

    $config = array();
    foreach ($this->patternConfigs as $pattern => $patternConfig)
    {
      if (preg_match('/^'.str_replace('/', '\\/', $pattern).'$/', $path) > 0)
      {
        $config = sfToolkit::arrayDeepMerge($config, $patternConfig);
      }
    }

    return $config;
  }

  protected function getPropsFromPath($path)
  {
    $props = array();

    $paths = array();
    $paths[] = dirname($path).'/.svn/props/'.basename($path).'.svn-work';
    $paths[] = dirname($path).'/.svn/prop-base/'.basename($path).'.svn-base';

    foreach ($paths as $path)
    {
      if (file_exists($path))
      {
        if (false === $handle = fopen($path, 'r'))
        {
          // TODO: Error handling
        }

        while (!feof($handle))
        {
          // Read a key length line.  Might be END, though.
          if (false === $buffer = fgets($handle))
          {
            // TODO: Error handling
          }

          // Check for the end of the hash
          if ("END\n" == $buffer)
          {
            break;
          }

          if ('K ' != substr($buffer, 0, 2))
          {
            // TODO: Error handling
          }

          // Now read that much into a buffer
          if (false === $key = fread($handle, substr($buffer, 2)))
          {
            // TODO: Error handling
          }

          // Suck up extra newline after key data
          if ("\n" != fgetc($handle))
          {
            // TODO: Error handling
          }

          // Read a value length line
          if (false === $buffer = fgets($handle))
          {
            // TODO: Error handling
          }

          if ('V ' != substr($buffer, 0, 2))
          {
            // TODO: Error handling
          }

          // Now read that much into a buffer
          if (false === $value = fread($handle, substr($buffer, 2)))
          {
            // TODO: Error handling
          }

          // Suck up extra newline after value data
          if ("\n" != fgetc($handle))
          {
            // TODO: Error handling
          }

          $props[$key] = $value;
        }

        if (false === fclose($handle))
        {
          // TODO: Error handling
        }
      }
    }

    return $props;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    // Call realpath() before constructing PHP_CodeSniffer because the
    // constructor changes our current working directory.  An argument handler
    // which sanitized user input would be nice:
    // http://trac.symfony-project.com/ticket/3486
    $arguments['path'] = realpath($arguments['path']);

    $phpcs = new PHP_CodeSniffer;

    $finder = new SvnFinder;
    foreach ($finder->in($arguments['path']) as $path)
    {
      $config = $this->getConfigForPath($path);
      if (!isset($config['code']['standard']) && !isset($config['preamble']) && !isset($config['props']))
      {
        continue;
      }

      // HACK: It is not easy to modify a file's token listeners after it is
      // constructed, so construct a populated file if the code standard is
      // defined, and an empty file otherwise
      if (isset($config['code']['standard']))
      {
        // HACK: PHP_CodeSniffer_File now expects an array of
        // PHP_CodeSniffer_Sniff instances, which
        // PHP_CodeSniffer::getTokenListeners() does not return
        $processPhpcs = new PHP_CodeSniffer;
        $processPhpcs->process(array(), $config['code']['standard']);
        $listeners = $processPhpcs->getTokenSniffs();

        $phpcsFile = new PHP_CodeSniffer_File($path, $listeners['file'], $phpcs->allowedFileExtensions);

        $phpcsFile->start();
      }
      else
      {
        $phpcsFile = new PHP_CodeSniffer_File($path, array(), $phpcs->allowedFileExtensions);
      }

      if (isset($config['preamble']))
      {
      }

      if (isset($config['props']))
      {
        $props = $this->getPropsFromPath($path);
        foreach ($props + $config['props'] as $key => $value)
        {
          if (isset($props[$key]) && !isset($config['props'][$key]))
          {
            $phpcsFile->addError('SVN property "'.$key.'" = "'.$props[$key].'" found but not expected', 0);
            continue;
          }

          if (!isset($props[$key]) && isset($config['props'][$key]))
          {
            $phpcsFile->addError('SVN property "'.$key.'" = "'.$config['props'][$key].'" expected but not found', 0);
            continue;
          }

          if ($props[$key] != $config['props'][$key])
          {
            $phpcsFile->addError('SVN property "'.$key.'" = "'.$props[$key].'" expected to match "'.$config['props'][$key].'"', 0);
          }
        }
      }

      $phpcs->addFile($phpcsFile);
    }

    $phpcs->printErrorReport();
  }
}
