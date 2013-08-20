<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2007 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ReleaseTask extends sfBaseTask
{
  /**
   * @see sfBaseTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('version', sfCommandArgument::REQUIRED, 'FIXME'),
      new sfCommandArgument('stability', sfCommandArgument::REQUIRED, 'FIXME')));

    $this->addOptions(array(
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
    ));
  }

  /**
   * @see sfBaseTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    // Confirmation
    if (
      !$options['no-confirmation']
      &&
      !$this->askConfirmation(array(
          'WARNING: Your changes in your local index and your working tree will',
          'be lost, including ignored files. Are you sure you want to proceed? (y/N)',
        ), 'QUESTION_LARGE', false)
    )
    {
      $this->logSection('release', 'Task aborted.');

      return 1;
    }

    $doc = new DOMDocument;
    $doc->load(sfConfig::get('sf_config_dir').'/package.xml');

    $xpath = new DOMXPath($doc);
    $xpath->registerNamespace('p', 'http://pear.php.net/dtd/package-2.0');

    $name = $xpath->evaluate('string(p:name)', $doc->documentElement);

    if (!$xpath->evaluate('boolean(p:date)', $doc->documentElement))
    {
      $dateNode = $doc->createElement('date', date('Y-m-d'));

      // Date element must immediately precede the optional time element or the
      // mandatory version element
      $timeOrVersionNode = $xpath->query('p:time | p:version', $doc->documentElement)->item(0);
      $doc->documentElement->insertBefore($dateNode, $timeOrVersionNode);
    }

    if (!$xpath->evaluate('boolean(p:version/p:release)', $doc->documentElement))
    {
      $releaseNode = $doc->createElement('release', $arguments['version']);

      $apiNode = $xpath->query('p:version/p:api', $doc->documentElement)->item(0);
      $apiNode->parentNode->insertBefore($releaseNode, $apiNode);
    }

    if (null === $stabilityNode = $xpath->query('p:stability', $doc->documentElement)->item(0))
    {
      $stabilityNode = $doc->createElement('stability');

      $licenseNode = $xpath->query('p:license', $doc->documentElement)->item(0);
      $doc->documentElement->insertBefore($stabilityNode, $licenseNode);
    }

    if (null === $apiNode = $xpath->query('p:api', $stabilityNode)->item(0))
    {
      $apiNode = $doc->createElement('api', $arguments['stability']);
      $stabilityNode->appendChild($apiNode);
    }

    if (!$xpath->evaluate('boolean(p:release)', $stabilityNode))
    {
      $releaseNode = $doc->createElement('release', $arguments['stability']);
      $stabilityNode->insertBefore($releaseNode, $apiNode);
    }

    // add class files
    if (null === $dirNode = $xpath->query('p:contents/p:dir', $doc->documentElement)->item(0))
    {
      $dirNode = $doc->createElement('dir');
      $dirNode->setAttribute('name', '/');

      $contentsNode = $xpath->query('p:contents', $doc->documentElement)->item(0);
      $contentsNode->appendChild($dirNode);
    }

    $patternNodes = array();
    foreach ($xpath->query('p:contents//p:file', $doc->documentElement) as $patternNode)
    {
      // Globs like //foo/... must be matched against paths with a leading
      // slash, while globs like foo/... must be matched against paths without
      // a leading slash.  Consequently, prefix all globs with slash, if
      // necessary, and always match against paths with a leading slash.
      if (strncmp($glob = $patternNode->getAttribute('name'), '/', 1) != 0)
      {
        $glob = '/'.$glob;
      }

      $pattern = $this->globToPattern($glob);
      $patternNodes[$pattern] = $patternNode;

      $patternNode->parentNode->removeChild($patternNode);
    }

    // FIXME: Switch back to SvnFinder when it supports externals
    $finder = new sfFinder;
    foreach ($finder->in(sfConfig::get('sf_root_dir')) as $path)
    {
      if (strncmp($path, sfConfig::get('sf_root_dir'), $len = strlen(sfConfig::get('sf_root_dir'))) == 0)
      {
        $path = substr($path, $len);
      }

      unset($fileNode);
      foreach ($patternNodes as $pattern => $patternNode)
      {
        if (preg_match('/^'.str_replace('/', '\\/', $pattern).'$/', $path) > 0)
        {
          if (!isset($fileNode))
          {
            $fileNode = $doc->createElement('file');
          }

          foreach ($patternNode->attributes as $attrNode)
          {
            $fileNode->setAttributeNode(clone $attrNode);
          }
        }
      }

      if (isset($fileNode))
      {
        $fileNode->setAttribute('name', ltrim($path, '/'));
        $dirNode->appendChild($fileNode);
      }
    }

    $packageXmlPath = sfConfig::get('sf_root_dir').'/package.xml';
    $doc->save($packageXmlPath);
    $this->logSection('release', sprintf('%s generated', $packageXmlPath));
  }

  protected function globToPattern($glob)
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
}
