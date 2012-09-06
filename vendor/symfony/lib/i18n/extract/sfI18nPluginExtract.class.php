<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony
 * @subpackage i18n
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfI18nApplicationExtract.class.php 14872 2009-01-19 08:32:06Z fabien $
 */
class sfI18nPluginExtract extends sfI18nExtract
{
  protected
    $extracts;

  /**
   * @see sfI18nExtract
   */
  public function configure()
  {
    if (!isset($this->parameters['path']))
    {
      throw new sfException('You must give a "path" parameter when extracting for a plugin.');
    }
  }

  /**
   * @see sfI18nExtract
   */
  protected function loadMessageSources()
  {
    $opt = $this->i18n->getOptions();
    $this->messageSource = sfMessageSource::factory($opt['source'], $this->parameters['path'].'/i18n');
    $this->messageSource->setCulture($this->culture);
    $this->messageSource->load();
  }

  /**
   * @see sfI18nExtract
   */
  public function extract()
  {
    foreach (sfFinder::type('dir')->maxdepth(0)->relative()->in($this->parameters['path'].'/modules') as $moduleName)
    {
      $this->extractFromPhpFiles(array(
        $this->parameters['path'].'/modules/'.$moduleName.'/actions',
        $this->parameters['path'].'/modules/'.$moduleName.'/lib',
        $this->parameters['path'].'/modules/'.$moduleName.'/templates',
      ));
    }

    $this->extractFromPhpFiles($this->parameters['path'].'/lib');
  }

  /**
   * @see sfI18nExtract
   */
  protected function loadCurrentMessages()
  {
    $this->currentMessages = array();
    foreach ($this->messageSource->read() as $catalogue => $translations)
    {
      foreach ($translations as $key => $values)
      {
        $this->currentMessages[] = $key;
      }
    }
  }

  /**
   * @see sfI18nExtract
   */
  public function saveNewMessages()
  {
    foreach ($this->getNewMessages() as $message)
    {
      $this->messageSource->append($message);
    }

    $this->messageSource->save();
  }

  /**
   * @see sfI18nExtract
   */
  public function deleteOldMessages()
  {
    foreach ($this->getOldMessages() as $message)
    {
      $this->messageSource->delete($message);
    }
  }
}
