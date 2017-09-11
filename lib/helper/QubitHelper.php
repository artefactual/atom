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

  $resourceRaw = sfOutputEscaper::unescape($resource);
  if (isset($resourceRaw) && $culture != $resourceRaw->sourceCulture)
  {
    try
    {
      $source = $resourceRaw->__get($options['name'], array('sourceCulture' => true));
      $fallback = $resourceRaw->__get($options['name']);
    }
    catch (Exception $e)
    {
      if ('Unknown record property' !== substr($e->getMessage(), 0, 23))
      {
        throw $e;
      }
    }

    if (0 < strlen($source) && 0 === strlen($fallback))
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

function render_show($label, $value, $options = array())
{
  // Optional labels in the div class containing this field, to help with data mining.
  $fieldLabel = isset($options['fieldLabel']) ? ' class="'.$options['fieldLabel'].'"' : '';

  $result = <<<contents
<div class="field">
  <h3>$label</h3>
  <div$fieldLabel>
    $value
  </div>
</div>

contents;

  return $result;
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
  $value = qubit_auto_link_text($value);

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
      $dataTitle[] = esc_entities($item->levelOfDescription->__toString());
    }

    if ((null !== $status = $item->getPublicationStatus()) && QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $status->statusId)
    {
      $dataTitle[] = esc_entities($item->getPublicationStatus()->__toString());
    }

    if (0 < count($dataTitle))
    {
      $node .= ' data-title="'.esc_entities(implode(' - ', $dataTitle)).'"';
    }
  }
  else if ($item instanceof QubitTerm)
  {
    $node .= ' data-title="'.esc_entities(sfConfig::get('app_ui_label_term')).'"';
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
    $node .= '<a href="#">';

    if (isset($options['numSiblingsLeft']))
    {
      $node .= sfContext::getInstance()->i18n->__('%1% more', array('%1%' => abs($options['numSiblingsLeft'])));
    }

    $node .= '...</a>';
  }
  else
  {
    $rawItem = sfOutputEscaper::unescape($item);
    if ($rawItem instanceof QubitInformationObject)
    {
      // Level of description
      if (null !== $levelOfDescription = QubitTerm::getById($item->levelOfDescriptionId))
      {
        $node .= '<span class="levelOfDescription">'.esc_specialchars($levelOfDescription->getName()).'</span>';
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
    else if ($rawItem instanceof QubitTerm)
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

function check_field_visibility($fieldName, $options = array())
{
  // Check always field if public option is set to true
  if (isset($options['public']) && $options['public'])
  {
    return sfConfig::get($fieldName, false);
  }

  return (is_using_cli() || sfContext::getInstance()->user->isAuthenticated()) || sfConfig::get($fieldName, false);
}

function get_search_i18n($hit, $fieldName, $options = array())
{
  // The default is to return "Untitled" unless allowEmpty is true
  $allowEmpty = true;
  if (isset($options['allowEmpty']))
  {
    $allowEmpty = $options['allowEmpty'];
  }

  // Use culture fallback? Default = true
  $cultureFallback = true;
  if (isset($options['cultureFallback']))
  {
    $cultureFallback = $options['cultureFallback'];
  }

  // Filter return value if empty
  $showUntitled = function($value = null) use ($allowEmpty)
  {
    if (null !== $value || 0 < count($value))
    {
      return $value->get(0);
    }

    if ($allowEmpty)
    {
      return '';
    }

    return sfContext::getInstance()->i18n->__('Untitled');
  };

  if (empty($hit))
  {
    return $showUntitled();
  }

  if ($hit instanceof sfOutputEscaperObjectDecorator && 'Elastica\Result' == $hit->getClass())
  {
    $hit = $hit->getData(); // type=sfOutputEscaperArrayDecorator
  }

  $accessField = function($culture) use ($hit, $fieldName)
  {
    if (is_object($hit) && 'sfOutputEscaperArrayDecorator' === get_class($hit))
    {
      $i18nRaw = $hit->getRaw('i18n');
      if (empty($i18nRaw[$culture][$fieldName]))
      {
        return false;
      }

      return $hit->get('i18n')->get($culture)->get($fieldName);
    }
    else
    {
      if (empty($hit['i18n'][$culture][$fieldName]))
      {
        return false;
      }

      return $hit['i18n'][$culture][$fieldName];
    }
  };

  if (isset($options['culture']))
  {
    $v = $accessField($options['culture']);
    if ($v)
    {
      return $v;
    }
  }

  $v = $accessField(sfContext::getInstance()->user->getCulture());
  if ($v)
  {
    return $v;
  }

  if ($cultureFallback)
  {
    $sourceCulture = is_object($hit) ? $hit->get('sourceCulture') : $hit['sourceCulture'];
    if (empty($sourceCulture))
    {
      return $showUntitled();
    }

    $v = $accessField($sourceCulture);
    if (false !== $v)
    {
      return $v;
    }
  }

  return $showUntitled();
}

function get_search_creation_details($hit, $culture = null)
{
  if (!isset($culture))
  {
    $culture = sfContext::getInstance()->user->getCulture();
  }

  if ($hit instanceof sfOutputEscaperObjectDecorator && 'Elastica\Result' == $hit->getClass())
  {
    $hit = $hit->getData(); // type=sfOutputEscaperArrayDecorator
  }

  $details = array();

  // Get creators
  $creators = $hit->get('creators');
  if (null !== $creators && 0 < count($creators))
  {
    $details[] = get_search_i18n($creators->get(0), 'authorizedFormOfName', array('allowEmpty' => false, 'cultureFallback' => true));
  }

  // WIP, we are not showing labels for now. See #5202.

  if (0 == count($details))
  {
    return null;
  }

  return implode(', ', $details);
}

function get_search_autocomplete_string($hit)
{
  if ($hit instanceof sfOutputEscaperObjectDecorator && 'Elastica\Result' == $hit->getClass())
  {
    $hit = $hit->getData(); // type=sfOutputEscaperArrayDecorator
  }

  $string = array();

  $levelOfDescriptionAndIdentifier = array();

  if (isset($hit['levelOfDescriptionId']))
  {
    $levelOfDescriptionAndIdentifier[] = QubitTerm::getById($hit['levelOfDescriptionId'])->__toString();
  }

  if ('1' == sfConfig::get('app_inherit_code_informationobject', 1)
    && isset($hit['referenceCode']) && !empty($hit['referenceCode']))
  {
    $levelOfDescriptionAndIdentifier[] = $hit['referenceCode'];
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

/**
 * qubit_auto_link_text is like TextHelper::auto_link_text(), but it uses
 * the local QubitHelper::qubit_auto_link_urls() instead of
 * TextHelper::_auto_link_urls().
 */
function qubit_auto_link_text($text, $link = 'all', $href_options = array())
{
  require_once(dirname(__FILE__).'/../../vendor/symfony/lib/helper/TextHelper.php');

  if ($link == 'all')
  {
    return qubit_auto_link_urls(_auto_link_email_addresses($text), $href_options);
  }
  else if ($link == 'email_addresses')
  {
    return _auto_link_email_addresses($text);
  }
  else if ($link == 'urls')
  {
    return qubit_auto_link_urls($text, $href_options);
  }
}

if (!defined('AR_AUTO_LINK_RE'))
{
  define('AR_AUTO_LINK_RE', '~
    (?:
      (                                             # Leading text
        <\w+.*?>|                                   #  - Leading HTML tag, or
        [^=!:\'"/]|                                 #  - Leading punctuation, or
        ^                                           #  - beginning of line
      )|                                            # Or Redmine hyperlink
      (?:&quot;|\")(?<label>.*?)(?:\&quot;|\")\:    #  - Double quote and colon
    )
    (
      (?:(?:https?|ftp)://)|                        # protocol spec, or
      (?:www\.)|                                    # www.*
      (?:mailto:)
    )
    (
      [-\w@]+                                       # subdomain or domain
      (?:\.[-\w@]+)*                                # remaining subdomains or domain
      (?::\d+)?                                     # port
      (?:/(?:(?:[\~\w\+%-]|(?:[,.;:][^\s$]))+)?)*   # path
      (?:\?[\w\+\/%&=.;-]+)?                        # query string
      (?:\#[\w\-/\?!=]*)?                           # trailing anchor
    )
    ([[:punct:]]|\s|<|$)                            # trailing text
   ~x');
}

function qubit_auto_link_urls($text, $href_options = array())
{
  require_once(dirname(__FILE__).'/../../vendor/symfony/lib/helper/TagHelper.php');

  $href_options = _tag_options($href_options);

  $callback_function = '
    if (!empty($matches[\'label\']))
    {
      return $matches[1].\'<a href="\'.$matches[3].$matches[4].\'">\'.$matches[\'label\'].\'</a>\'.$matches[5];
    }

    if (preg_match("/<a\s/i", $matches[1]))
    {
      return $matches[0];
    }
    else
    {
      return $matches[1].\'<a href="\'.($matches[3] == "www." ? "http://www." : $matches[3]).$matches[4].\'"'.$href_options.'>\'.$matches[3].$matches[4].\'</a>\'.$matches[5];
    }
    ';

  return preg_replace_callback(
    AR_AUTO_LINK_RE,
    create_function('$matches', $callback_function),
    $text);
}

function render_search_result_date($date)
{
  $date = sfOutputEscaper::unescape($date);

  if (empty($date))
  {
    return;
  }

  foreach ((array)$date as $item)
  {
    $displayDate = get_search_i18n($item, 'date');
    $startDate = isset($item['startDateString']) ? $item['startDateString'] : null;
    $endDate = isset($item['endDateString']) ? $item['endDateString'] : null;

    if (empty($displayDate) && empty($startDate) && empty($endDate))
    {
      continue;
    }

    return Qubit::renderDateStartEnd($displayDate, $startDate, $endDate);
  }
}
