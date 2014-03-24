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

class qubitConfiguration extends sfApplicationConfiguration
{
  const
    // Required format: x.y.z
    VERSION = '2.1.0';

  public function responseFilterContent(sfEvent $event, $content)
  {
    ProjectConfiguration::getActive()->loadHelpers('Javascript');

    $drmc = array();
    foreach (sfConfig::getAll() as $key => $value)
    {
      if (strpos($key, 'app_drmc_') === 0)
      {
        $key = substr($key, 9);
        $drmc[$key] = $value;
      }
    }

    $data = json_encode(array(
      'relativeUrlRoot' => sfContext::getInstance()->request->getRelativeUrlRoot(),
      'frontend' => sfContext::getInstance()->controller->genUrl('@homepage'),
      'drmc' => $drmc
    ));

    return str_ireplace('<head>', '<head>'.javascript_tag(<<<EOF
var Qubit = $data;
EOF
    ), $content);
  }

  /**
   * @see sfApplicationConfiguration
   */
  public function configure()
  {
    $this->dispatcher->connect('response.filter_content', array($this, 'responseFilterContent'));

    $this->dispatcher->connect('access_log.view', array('QubitAccessLogObserver', 'view'));
  }

  /**
   * @see sfApplicationConfiguration
   */
  public function initialize()
  {
    if (false !== $readOnly = getenv('ATOM_READ_ONLY'))
    {
      sfConfig::set('app_read_only', filter_var($readOnly, FILTER_VALIDATE_BOOLEAN));
    }

    $this->bootstrapDrmc();
  }

  /**
   * @see sfApplicationConfiguration
   */
  public function getControllerDirs($moduleName)
  {
    if (!isset($this->cache['getControllerDirs'][$moduleName]))
    {
      $this->cache['getControllerDirs'][$moduleName] = array();

      // HACK Currently plugins only override application templates, not the
      // other way around
      foreach ($this->getPluginSubPaths('/modules/'.$moduleName.'/actions') as $dir)
      {
        $this->cache['getControllerDirs'][$moduleName][$dir] = false; // plugins
      }

      $this->cache['getControllerDirs'][$moduleName][sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/actions'] = false; // application
    }

    return $this->cache['getControllerDirs'][$moduleName];
  }

  /**
   * @see sfApplicationConfiguration
   */
  public function getDecoratorDirs()
  {
    $dirs = sfConfig::get('sf_decorator_dirs');
    $dirs[] = sfConfig::get('sf_app_template_dir');

    return $dirs;
  }

  /**
   * @see sfApplicationConfiguration
   */
  public function getTemplateDirs($moduleName)
  {
    // HACK Currently plugins only override application templates, not the
    // other way around
    $dirs = $this->getPluginSubPaths('/modules/'.$moduleName.'/templates');
    $dirs[] = sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/templates';

    $dirs = array_merge($dirs, $this->getDecoratorDirs());

    return $dirs;
  }

  /**
   * @see sfProjectConfiguration
   */
  public function setRootDir($path)
  {
    parent::setRootDir($path);

    $this->setWebDir($path);
  }

  protected function bootstrapDrmc()
  {
    // Anticipate connection to the database, we are going to need it
    // I hope this is not breaking anything :)
    try
    {
      $databaseManager = new sfDatabaseManager($this);
      $conn = $databaseManager->getDatabase('propel')->getConnection();
    }
    catch (Exception $e)
    {
      return;
    }

    // Load env ATOM_DRMC_TMS_URL, defaults to "http://vmsqlsvcs.museum.moma.org/TMSAPI/TmsObjectSvc/TmsObjects.svc"
    if (false === $envDrmcTmsUrl = getenv('ATOM_DRMC_TMS_URL'))
    {
      $envDrmcTmsUrl = 'http://vmsqlsvcs.museum.moma.org/TMSAPI/TmsObjectSvc/TmsObjects.svc';
    }
    $envDrmcTmsUrl = filter_var($envDrmcTmsUrl, FILTER_VALIDATE_URL);
    if (false === $envDrmcTmsUrl)
    {
      throw new sfException('ATOM_DRMC_TMS_URL doesn\'t seem to be a valid URL');
    }
    sfConfig::set('app_drmc_tms_url', $envDrmcTmsUrl);

    /**
     * Adding configuration to sfConfig (caching)
     */
    try
    {
      $cache = QubitCache::getInstance();
    }
    catch (Exception $e)
    {

    }

    $cacheKey = 'config_drmc';

    // Hit the cache if config_drmc is available
    if (isset($cache) && $cache->has($cacheKey))
    {
      $cacheableParams = unserialize($cache->get($cacheKey));
    }
    else
    {
      $cachableParams = array();

      // Levels of descriptions
      $criteria = new Criteria;
      $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID);
      foreach (QubitTerm::get($criteria) as $item)
      {
        $slug = str_replace('-', '_', QubitSlug::slugify($item->getName(array('culture' => 'en'))));
        if (1 > strlen($slug))
        {
          continue;
        }
        $configurationId = 'app_drmc_lod_'.$slug.'_id';

        $cacheableParams[$configurationId] = $item->id;
      }

      // Taxonomies
      $taxonomies = array(
        'Classifications',
        'Departments',
        'Component types');

      foreach ($taxonomies as $name)
      {
        $criteria = new Criteria;
        $criteria->addJoin(QubitTaxonomy::ID, QubitTaxonomyI18n::ID);
        $criteria->add(QubitTaxonomyI18n::CULTURE, 'en');
        $criteria->add(QubitTaxonomyI18n::NAME, $name);

        if (null !== $taxonomy = QubitTaxonomy::getOne($criteria))
        {
          $slug = str_replace('-', '_', QubitSlug::slugify($taxonomy->getName(array('culture' => 'en'))));
          if (1 > strlen($slug))
          {
            continue;
          }
          $configurationId = 'app_drmc_taxonomy_'.$slug.'_id';

          $cacheableParams[$configurationId] = $taxonomy->id;
        }
      }

      // Terms
      $terms = array(
        QubitTaxonomy::NOTE_TYPE_ID => array(
          'InstallComments',
          'PrepComments',
          'StorageComments'
        ),
        QubitTaxonomy::RELATION_TYPE_ID => array(
          'Supporting technology relation types'
        )
      );

      foreach ($terms as $taxonomyId => $name)
      {
        $criteria = new Criteria;
        $criteria->add(QubitTerm::TAXONOMY_ID, $taxonomyId);
        $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
        $criteria->add(QubitTermI18n::CULTURE, 'en');
        $criteria->add(QubitTermI18n::NAME, $name);

        if (null !== $term = QubitTerm::getOne($criteria))
        {
          $slug = str_replace('-', '_', QubitSlug::slugify($term->getName(array('culture' => 'en'))));
          if (1 > strlen($slug))
          {
            continue;
          }
          $configurationId = 'app_drmc_note_type_'.$slug.'_id';

          $cacheableParams[$configurationId] = $term->id;
        }
      }

      // Cache
      if (isset($cache))
      {
        $cache->set($cacheKey, serialize($cacheableParams));
      }
    }

    // Dump $cacheableParams in sfConfig
    foreach ($cacheableParams as $key => $value)
    {
      sfConfig::set($key, $value);
    }
  }
}
