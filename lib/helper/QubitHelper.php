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

function format_script($script_iso, $culture = null)
{
  $c = sfCultureInfo::getInstance($culture === null ? sfContext::getInstance()->user->getCulture() : $culture);
  $scripts = $c->getScripts();

  if (!isset($scripts[$script_iso]))
  {
    $c = sfCultureInfo::getInstance(sfConfig::get('sf_default_culture'));
    $scripts = $c->getScripts();
  }

  return isset($scripts[$script_iso]) ? $scripts[$script_iso] : '';
}

function render_field($field, $resource, array $options = array())
{
  $options += array('name' => $field->getName());

  $div = null;
  $culture = sfContext::getInstance()->user->getCulture();

  if (isset($resource)
      && $culture != $resource->sourceCulture
        && 0 < strlen($source = $resource->__get($options['name'], array('sourceCulture' => true)))
          && 0 == strlen($resource->__get($options['name'])))
  {
    // TODO Are there cases where the direction of this <div/>'s containing
    // block isn't the direction of the current culture?
    $dir = null;
    $sourceCultureInfo = sfCultureInfo::getInstance($resource->sourceCulture);
    if (sfCultureInfo::getInstance($culture)->direction != $sourceCultureInfo->direction)
    {
      $dir = " dir=\"$sourceCultureInfo->direction\"";
    }

    $div = <<<div
<div class="default-translation"$dir>
  $source
</div>

div;
  }

  unset($options['name']);

  if (isset($options['onlyInput']) && $options['onlyInput'])
  {
    $field = $div.$field->render($options);
  }
  else
  {
    $field = '<div class="form-item">'.$field->renderLabel().$field->renderError().
                  $div.$field->render($options).$field->renderHelp().'</div>';
  }

  return $field;
}

function render_show($label, $value)
{
  return <<<return
<div class="field">
  <h3>$label</h3>
  <div>
    $value
  </div>
</div>

return;
}

function render_show_repository($label, $resource)
{
  if (isset($resource->repository))
  {
    return render_show($label, link_to(render_title($resource->repository), array($resource->repository, 'module' => 'repository')));
  }

  foreach ($resource->ancestors->orderBy('rgt') as $item)
  {
    if (isset($item->repository))
    {
      return render_show($label, link_to(render_title($item->repository), array($item->repository, 'module' => 'repository'), array('title' => __('Inherited from %1%', array('%1%' => $item)))));
    }
  }
}

function render_title($value, $html = true)
{
  // TODO Workaround for PHP bug, http://bugs.php.net/bug.php?id=47522
  // Also, method_exists is very slow if a string is passed (class lookup), use is_object
  if (is_object($value) && method_exists($value, '__toString'))
  {
    $value = $value->__toString();
  }

  if (0 < strlen($value))
  {
    return (string) $value;
  }

  return ($html ? '<em>' : '').sfContext::getInstance()->i18n->__('Untitled').($html ? '</em>' : '');
}

function render_value($value)
{
  ProjectConfiguration::getActive()->loadHelpers('Text');

  $value = auto_link_text($value);

  // Simple lists
  $value = preg_replace('/(?:^\*.*\r?\n)*(?:^\*.*)/m', "<ul>\n$0\n</ul>", $value);
  $value = preg_replace('/(?:^-.*\r?\n)*(?:^-.*)/m', "<ul>\n$0\n</ul>", $value);
  $value = preg_replace('/^(?:\*|-)\s*(.*)(?:\r?\n)?/m', '<li>$1</li>', $value);

  $value = preg_replace('/(?:\r?\n){2,}/', "</p><p>", $value, -1, $count);
  if (0 < $count)
  {
    $value = "<p>$value</p>";
  }

  $value = preg_replace('/\r?\n/', '<br/>', $value);

  return $value;
}

/**
 * Return a human readable file size, using the appropriate SI prefix
 *
 * @param integer $val value in bytes
 * @return string human-readable value with units
 */
function hr_filesize($val)
{
  $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB');
  for ($i = 0; $i < count($units); $i++)
  {
    if ($val / pow(1024, $i + 1) < 1)
    {
      break;
    }
  }

  return round(($val / pow(1024, $i)), 1).' '.$units[$i];
}

function render_treeview_node($item, array $classes = array(), array $options = array())
{
  // Build array of classes
  $_classes = array();
  foreach ($classes as $key => $value)
  {
    if ($value)
    {
      $_classes[$key] = $key;
    }
  }

  // Start HTML list element
  $node = '<li';

  // Create class attribute from $classes array
  if (0 < count($_classes))
  {
    $node .= ' class="'.implode(' ', $_classes).'"';
  }

  // Add data-xhr-location if exists
  if (isset($options['xhr-location']))
  {
    $node .= ' data-xhr-location="'.esc_entities($options['xhr-location']).'"';
  }

  if ($item instanceof QubitInformationObject)
  {
    $dataTitle = array();

    if (isset($item->levelOfDescription))
    {
      $dataTitle[] = $item->levelOfDescription->__toString();
    }

    if ((null !== $status = $item->getPublicationStatus()) && QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $status->statusId)
    {
      $dataTitle[] = $item->getPublicationStatus()->__toString();
    }

    if (0 < count($dataTitle))
    {
      $node .= ' data-title="'.esc_entities(implode(' - ', $dataTitle)).'"';
    }
  }
  else if ($item instanceof QubitTerm)
  {
    $node .= ' data-title="'.sfConfig::get('app_ui_label_term').'"';
  }

  $node .= ' data-content="'.esc_entities(render_title($item)).'"';

  // Close tag
  $node .= '>';

  // Add <i> tag if the node is expandable
  if (isset($_classes['expand']) || isset($_classes['ancestor']))
  {
    $node .= '<i></i>&nbsp;';
  }

  if (isset($_classes['more']))
  {
    $node .= '<a href="#">...</a>';
  }
  else
  {
    if ($item instanceof QubitInformationObject)
    {
      // Level of description
      if (null !== $levelOfDescription = QubitTerm::getById($item->levelOfDescriptionId))
      {
        $node .= '<span class="levelOfDescription">'.$levelOfDescription->getName().'</span>';
      }

      // Title
      $title = '';
      if ($item->identifier)
      {
        $title = $item->identifier . "&nbsp;-&nbsp;";
      }
      $title .= render_title($item);

      // Add link
      $node .= link_to($title, array($item, 'module' => 'informationobject'), array('title' => null));

      // Publication status
      if ((null !== $status = $item->getPublicationStatus()) && QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $status->statusId)
      {
        $node .= '<span class="pubStatus">('.$status->__toString().')</span>';
      }
    }
    else if ($item instanceof QubitTerm)
    {
      $action = isset($options['browser']) && true === $options['browser'] ? 'browseTerm' : 'index';

      // Add link
      $node .= link_to(render_title($item), array($item, 'module' => 'term', 'action' => $action));
    }
  }

  // Close node tag
  $node .= '</li>';

  return $node;
}

function is_using_cli()
{
  return php_sapi_name() === 'cli';
}

function check_field_visibility($fieldName)
{
  return (is_using_cli() || sfContext::getInstance()->user->isAuthenticated()) || sfConfig::get($fieldName, false);
}

function get_search_i18n($hit, $fieldName, $cultureFallback = true, $allowEmpty = true, $selectedCulture = null)
{
  $userCulture = sfContext::getInstance()->user->getCulture();

  if ($hit instanceof \Elastica\Result)
  {
    $hit = $hit->getData();
  }

  $value = null;

  if (isset($selectedCulture) && isset($hit['i18n'][$selectedCulture][$fieldName]))
  {
    $value = $hit['i18n'][$selectedCulture][$fieldName];
  }
  else if (isset($hit['i18n'][$userCulture][$fieldName]))
  {
    $value = $hit['i18n'][$userCulture][$fieldName];
  }
  else if ($cultureFallback && isset($hit['i18n'][$hit['sourceCulture']][$fieldName]))
  {
    $value = $hit['i18n'][$hit['sourceCulture']][$fieldName];
  }

  if (!$allowEmpty && ($value == null || $value == ''))
  {
    $value = sfContext::getInstance()->i18n->__('Untitled');
  }

  return $value;
}

function get_search_i18n_highlight(\Elastica\Result $hit, $fieldName, $culture = null)
{
  if (!isset($culture))
  {
    $culture = sfContext::getInstance()->user->getCulture();
  }

  $highlights = $hit->getHighlights();
  $field = 'i18n.'.$culture.'.'.$fieldName;

  if (isset($highlights[$field]))
  {
    return $highlights[$field][0];
  }
}

function get_search_creation_details($hit, $culture = null)
{
  if (!isset($culture))
  {
    $culture = sfContext::getInstance()->user->getCulture();
  }

  if ($hit instanceof \Elastica\Result)
  {
    $hit = $hit->getData();
  }

  $details = array();

  // Get creator
  if (isset($hit['creators']) && 0 < count($hit['creators']))
  {
    $creator = array_pop($hit['creators']);

    $details[] = get_search_i18n($creator, 'authorizedFormOfName', true, true, $culture);
  }

  // WIP, we are not showing labels for now. See #5202.
  if (0 == count($details)) return null;
  return implode(', ', $details);

  ProjectConfiguration::getActive()->loadHelpers('Date');

  // Get dates
  if (isset($hit['dates']))
  {
    foreach ($hit['dates'] as $item)
    {
      if (QubitTerm::CREATION_ID == $item['typeId'])
      {
        if (isset($item['date']))
        {
          $details[] = $item['date'];
        }
        elseif (isset($item['startDate']) && isset($item['endDate']))
        {
          $details[] = Qubit::renderDateStartEnd(null,
            format_date(strtotime($item['startDate']), 'yyyy-M-dd'),
            format_date(strtotime($item['endDate']), 'yyyy-M-dd'));
        }
      }

      // For now let's just print the first match
      break;
    }
  }

  if (0 == count($details))
  {
    return null;
  }

  return implode(', ', $details);
}

function get_search_autocomplete_string($hit)
{
  if ($hit instanceof \Elastica\Result)
  {
    $hit = $hit->getData();
  }

  $string = array();

  $levelOfDescriptionAndIdentifier = array();

  if (isset($hit['levelOfDescriptionId']))
  {
    $levelOfDescriptionAndIdentifier[] = QubitTerm::getById($hit['levelOfDescriptionId'])->__toString();
  }

  if ('1' == sfConfig::get('app_inherit_code_informationobject', 1)
    && isset($hit['inheritReferenceCode']) && !empty($hit['inheritReferenceCode']))
  {
    $levelOfDescriptionAndIdentifier[] = $hit['inheritReferenceCode'];
  }
  elseif (isset($hit['identifier']) && !empty($hit['identifier']))
  {
    $levelOfDescriptionAndIdentifier[] = $hit['identifier'];
  }

  if (0 < count($levelOfDescriptionAndIdentifier))
  {
    $string[] = implode($levelOfDescriptionAndIdentifier, ' ');
  }

  $titleAndPublicationStatus = array();

  if (null !== $title = get_search_i18n($hit, 'title'))
  {
    $titleAndPublicationStatus[] = $title;
  }

  if (isset($hit['publicationStatusId']) && QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $hit['publicationStatusId'])
  {
    $titleAndPublicationStatus[] = '('.QubitTerm::getById($hit['publicationStatusId'])->__toString().')';
  }

  if (0 < count($titleAndPublicationStatus))
  {
    $string[] = implode($titleAndPublicationStatus, ' ');
  }

  return implode(' - ', $string);
}

function escape_dc($text)
{
  return preg_replace('/\n/', '<lb/>', $text);
}
