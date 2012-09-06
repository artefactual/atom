<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Special test browser to aid in sfSearch functional testing
 *
 * @package sfSearch
 * @subpackage Test
 * @author Carl Vondrick
 */
class xfTestBrowser extends sfTestBrowser
{
  /**
   * Customizes the generator configuration
   *
   * @param $params
   */
  public function customizeGenerator(array $params)
  {
    if (!isset($params['index_class']))
    {
      $params['index_class'] = 'TestSearch';
    }

    $params['moduleName']  = 'search';

    sfToolkit::clearDirectory(sfConfig::get('sf_app_cache_dir'));

    $generatorManager = new sfGeneratorManager($this->getContext()->getConfiguration());

    if (!is_dir(sfConfig::get('sf_config_cache_dir')))
    {
      mkdir(sfConfig::get('sf_config_cache_dir'), 0777);
    }

    $filename = sprintf('%s/modules_%s_config_generator.yml.php', sfConfig::get('sf_config_cache_dir'), $params['moduleName']);
    $content = '<?php ' . sfGeneratorConfigHandler::getContent($generatorManager, 'xfGeneratorInterface', $params);

    file_put_contents($filename, $content);

    return $this;
  }
}
