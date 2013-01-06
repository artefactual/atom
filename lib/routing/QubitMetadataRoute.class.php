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

class QubitMetadataRoute extends sfRoute
{
  public static

    $METADATA_PLUGINS = array(
      'isaar' => 'sfIsaarPlugin',
      'eac'   => 'sfEacPlugin',
      'ead'   => 'sfEadPlugin',
      'isad'  => 'sfIsadPlugin',
      'dc'    => 'sfDcPlugin',
      'skos'  => 'sfSkosPlugin',
      'rad'   => 'sfRadPlugin',
      'mods'  => 'sfModsPlugin',
      'isdf'  => 'sfIsdfPlugin'),

    $DEFAULT_MODULES = array( // informationobject not needed
      'actor'      => 'sfIsaarPlugin',
      'repository' => 'sfIsdiahPlugin',
      'function'   => 'sfIsdfPlugin');

  /**
   * After parent::matches() does all the important stuff, find the module
   * that fits with the object (if a slug is given). If the slug is missing,
   * update the module to corresponding plugin module.
   *
   * Case 1: e.g. uri "/peanut-12345" (QubitInformationObject)
   *     -> Add module 'sfIsadPlugin' since that is the module that corresponds
   *        with that description (it looks at the record settings and the
   *        default application template).
   * Case 2: e.g. uri "/repository/add"
   *     -> Replace module "repository" with "sfIsdiahPlugin". The relation
   *        is descrbied in self::$DEFAULT_MODULES
   *
   * @see sfRoute
   */
  public function matchesUrl($url, $context = array())
  {
    if (false === $parameters = parent::matchesUrl($url, $context))
    {
      return false;
    }

    if (isset($parameters['slug']))
    {
      $criteria = new Criteria;
      $criteria->add(QubitSlug::SLUG, $parameters['slug']);
      $criteria->addJoin(QubitSlug::OBJECT_ID, QubitObject::ID);

      if (null === $this->resource = QubitObject::get($criteria)->__get(0))
      {
        return false;
      }

      // Find the Symfony module to be used based in the object class.
      // In some cases, the metadata template passed as part of the URL will be
      // considered. Additionally, QubitInformationObject will be analyzed
      // differently, since every object may have a specific metadata template
      // assigned.
      switch (true)
      {
        case $this->resource instanceof QubitRepository:
          $parameters['module'] = 'sfIsdiahPlugin';

          break;

        case $this->resource instanceof QubitDonor:
          $parameters['module'] = 'donor';

          break;

        case $this->resource instanceof QubitRightsHolder:
          $parameters['module'] = 'rightsholder';

          break;

        case $this->resource instanceof QubitUser:
          $parameters['module'] = 'user';

          break;

        case $this->resource instanceof QubitActor:
          $parameters['module'] = $this->getActionParameter(array('isaar', 'eac'), $this->getDefaultTemplate('actor'), $parameters);

          break;

        case $this->resource instanceof QubitFunction:
          $parameters['module'] = 'sfIsdfPlugin';

          break;

        case $this->resource instanceof QubitInformationObject:
          $default = $this->getDefaultTemplate('informationobject');

          // See if there is a template assigned in the database for this record
          $sql = 'SELECT code
            FROM information_object JOIN term ON information_object.source_metadata_id = term.id
            WHERE information_object.id = ? AND taxonomy_id = ?';

          if (false !== $defaultSetting = QubitPdo::fetchColumn($sql, array($this->resource->id, QubitTaxonomy::INFORMATION_OBJECT_TEMPLATE_ID)))
          {
            $default = $defaultSetting;
          }

          $parameters['module'] = $this->getActionParameter(array('isad', 'dc', 'mods', 'rad'), $default, $parameters);

          break;

        case $this->resource instanceof QubitAccession:
          $parameters['module'] = 'accession';

          break;

        case $this->resource instanceof QubitDeaccession:
          $parameters['module'] = 'deaccession';

          break;

        case $this->resource instanceof QubitTerm:
          $parameters['module'] = 'term';

          break;

        case $this->resource instanceof QubitTaxonomy:
          $parameters['module'] = 'taxonomy';

          break;

        case $this->resource instanceof QubitStaticPage:
          $parameters['module'] = 'staticpage';

          break;

        default:
          $this->forward404();
      }
    }

    // Given the parent module (e.g. informationobject), find the best metadata
    // to be used
    else if (isset($parameters['module']))
    {
      switch ($parameters['module'])
      {
        case 'informationobject':
          if (false !== $code = $this->getDefaultTemplate($parameters['module']))
          {
            $parameters['module'] = self::$METADATA_PLUGINS[$code];
          }

          break;

        case 'actor':
        case 'repository':
        case 'function':
          $module = $parameters['module'];
          $parameters['module'] = self::$DEFAULT_MODULES[$module];

          break;

      }
    }

    return $parameters;
  }

  /**
   * @see sfRoute
   */
  public function matchesParameters($params, $context = array())
  {
    $params = $this->parseParameters($params);

    return parent::matchesParameters($params, $context);
  }

  /**
   * @see sfRoute
   */
  public function generate($params, $context = array(), $absolute = false)
  {
    $params = $this->parseParameters($params);

    return parent::generate($params, $context, $absolute);
  }

  protected function parseParameters($params)
  {
    // When an object is passed, replace it with its slug
    if (isset($params[0]) && is_object($params[0]))
    {
      // Extract the slug property and pass it to generate()
      $params['slug'] = $params[0]->slug;

      // Don't show the module in the URL but analyze it so we can assing a
      // template in case that we know its associated plugin (module) name
      if (isset($params['module']))
      {
        if (false !== $key = array_search($params['module'], self::$METADATA_PLUGINS))
        {
          $params['template'] = $key;
        }

        unset($params['module']);
      }

      // Unset the object
      unset($params[0]);
    }

    return $params;
  }

  protected function getActionParameter($allowedValues, $default, $parameters)
  {
    $code = $default;

    if (isset($parameters['template']))
    {
      $code = $parameters['template'];
    }

    if (!in_array($code, $allowedValues))
    {
      throw new sfConfigurationException(sprintf('The metadata code "%s" is not valid.', $code));
    }

    return self::$METADATA_PLUGINS[$code];
  }

  /**
   * Fetch the default template for a given module from the database/settings
   * TODO: this should be cached somewhere
   */
  protected function getDefaultTemplate($module)
  {
    $sql = 'SELECT value
      FROM setting JOIN setting_i18n ON setting.id = setting_i18n.id
      WHERE scope = "default_template" AND name = ?';

    return QubitPdo::fetchColumn($sql, array($module));
  }
}
