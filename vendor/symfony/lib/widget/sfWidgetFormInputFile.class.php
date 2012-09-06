<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormInputFile represents an upload HTML input tag.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfWidgetFormInputFile.class.php 30762 2010-08-25 12:33:33Z fabien $
 */
class sfWidgetFormInputFile extends sfWidgetFormInput
{
  protected function getBytes($value)
  {
    $value = trim($value);
    switch (strtolower($value[strlen($value) - 1]))
    {
      // The 'G' modifier is available since PHP 5.1.0
      case 'g':
        $value *= 1024;
      case 'm':
        $value *= 1024;
      case 'k':
        $value *= 1024;
    }

    return $value;
  }

  /**
   * Configures the current widget.
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormInput
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->setOption('type', 'file');
    $this->setOption('needs_multipart', true);

    $size = $this->getBytes(ini_get('post_max_size'));

    $uploadMaxFilesize = $this->getBytes(ini_get('upload_max_filesize'));
    if (0 < $uploadMaxFilesize)
    {
      $size = $uploadMaxFilesize;
    }

    foreach (array('bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB') as $unit)
    {
      // 0.9# if > 1000, ### if < 1000
      if (1000 > $size)
      {
        break;
      }

      $size /= 1024;
    }

    $this->addOption('help', 'The maximum size of file uploads is '.round($size, 2).' '.$unit.'.');
  }
}
