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
    $c = sfCultureInfo::getInstance(null === $culture ? sfContext::getInstance()->user->getCulture() : $culture);
    $scripts = $c->getScripts();

    if (!isset($scripts[$script_iso])) {
        $c = sfCultureInfo::getInstance(sfConfig::get('sf_default_culture'));
        $scripts = $c->getScripts();
    }

    return isset($scripts[$script_iso]) ? $scripts[$script_iso] : '';
}

function render_field($field, $resource, array $options = [])
{
    $options += ['name' => $field->getName()];

    $div = null;
    $culture = sfContext::getInstance()->user->getCulture();

    $resourceRaw = sfOutputEscaper::unescape($resource);
    if (isset($resourceRaw) && $culture != $resourceRaw->sourceCulture) {
        try {
            $source = $resourceRaw->__get($options['name'], ['sourceCulture' => true]);
            $fallback = $resourceRaw->__get($options['name']);
        } catch (Exception $e) {
            if ('Unknown record property' !== substr($e->getMessage(), 0, 23)) {
                throw $e;
            }
        }

        if (0 < strlen($source) && 0 === strlen($fallback)) {
            // TODO Are there cases where the direction of this <div/>'s containing
            // block isn't the direction of the current culture?
            $dir = null;
            $sourceCultureInfo = sfCultureInfo::getInstance($resource->sourceCulture);
            if (sfCultureInfo::getInstance($culture)->direction != $sourceCultureInfo->direction) {
                $dir = " dir=\"{$sourceCultureInfo->direction}\"";
            }

            $div = <<<div
<div class="default-translation"{$dir}>
  {$source}
</div>

div;
        }
    }

    unset($options['name']);

    if (isset($options['onlyInput']) && $options['onlyInput']) {
        $field = $div.$field->render($options);
    } else {
        $field = '<div class="form-item">'.$field->renderLabel().$field->renderError()
            .$div.$field->render($options).$field->renderHelp().'</div>';
    }

    return $field;
}

function render_show($label, $value, $options = [])
{
    // Optional labels in the div class containing this field, to help with data mining.
    $fieldLabel = isset($options['fieldLabel']) ? ' class="'.$options['fieldLabel'].'"' : '';

    return <<<contents
<div class="field">
  <h3>{$label}</h3>
  <div{$fieldLabel}>
    {$value}
  </div>
</div>

contents;
}

function render_show_repository($label, $resource)
{
    if (isset($resource->repository)) {
        return render_show($label, link_to(render_title($resource->repository), [$resource->repository, 'module' => 'repository']));
    }

    foreach ($resource->ancestors->orderBy('rgt') as $item) {
        if (isset($item->repository)) {
            return render_show($label, link_to(render_title($item->repository), [$item->repository, 'module' => 'repository'], ['title' => __('Inherited from %1%', ['%1%' => $item])]));
        }
    }
}

function render_title($value, $renderMarkdown = true)
{
    $value = ($renderMarkdown) ? render_value_inline($value) : $value;

    if (0 < strlen($value)) {
        return $value;
    }

    return '<em>'.sfContext::getInstance()->i18n->__('Untitled').'</em>';
}

function render_value($value)
{
    // Parse using Parsedown's text method in safe mode
    $value = QubitMarkdown::getInstance()->parse($value);

    return add_paragraphs_and_linebreaks($value);
}

function render_value_inline($value)
{
    // Parse using Parsedown's inline method in safe mode
    $options = ['inline' => true];

    return QubitMarkdown::getInstance()->parse($value, $options);
}

function render_value_html($value)
{
    // Parse using Parsedown's text method in unsafe mode
    $options = ['safeMode' => false];
    $value = QubitMarkdown::getInstance()->parse($value, $options);

    return add_paragraphs_and_linebreaks($value);
}

function add_paragraphs_and_linebreaks($value)
{
    // Add paragraphs
    $value = preg_replace('/(?:\r?\n){2,}/', '</p><p>', $value, -1, $count);
    if (0 < $count) {
        $value = "<p>{$value}</p>";
    }

    // Maintain linebreaks not surrounded by tags
    return preg_replace('/(?!>)\r?\n(?!<)/', '<br/>', $value);
}

function strip_markdown($value)
{
    return QubitMarkdown::getInstance()->strip($value);
}

/**
 * Return a human readable file size, using the appropriate SI prefix.
 *
 * @param int $val value in bytes
 *
 * @return string human-readable value with units
 */
function hr_filesize($val)
{
    $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB'];
    for ($i = 0; $i < count($units); ++$i) {
        if ($val / pow(1024, $i + 1) < 1) {
            break;
        }
    }

    return round(($val / pow(1024, $i)), 1).' '.$units[$i];
}

function render_treeview_node($item, array $classes = [], array $options = [])
{
    // Build array of classes
    $_classes = [];
    foreach ($classes as $key => $value) {
        if ($value) {
            $_classes[$key] = $key;
        }
    }

    // Start HTML list element
    $node = '<li';

    // Create class attribute from $classes array
    if (0 < count($_classes)) {
        $node .= ' class="'.implode(' ', $_classes).'"';
    }

    // Add data-xhr-location if exists
    if (isset($options['xhr-location'])) {
        $node .= ' data-xhr-location="'.esc_entities($options['xhr-location']).'"';
    }

    if ($item instanceof QubitInformationObject) {
        $dataTitle = [];

        if (isset($item->levelOfDescription)) {
            $dataTitle[] = render_title($item->levelOfDescription);
        }

        if ((null !== $status = $item->getPublicationStatus()) && QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $status->statusId) {
            $dataTitle[] = render_title($item->getPublicationStatus());
        }

        if (0 < count($dataTitle)) {
            $node .= ' data-title="'.strip_tags((implode(' - ', $dataTitle))).'"';
        }
    } elseif ($item instanceof QubitTerm) {
        $node .= ' data-title="'.esc_entities(sfConfig::get('app_ui_label_term')).'"';
    }

    $node .= ' data-content="'.strip_markdown($item).'"';

    // Close tag
    $node .= '>';

    // Add <i> tag if the node is expandable
    if (isset($_classes['expand']) || isset($_classes['ancestor'])) {
        $node .= '<i></i>&nbsp;';
    }

    if (isset($_classes['more'])) {
        $node .= '<a href="#">';

        if (isset($options['numSiblingsLeft'])) {
            $node .= sfContext::getInstance()->i18n->__('%1% more', ['%1%' => abs($options['numSiblingsLeft'])]);
        }

        $node .= '...</a>';
    } else {
        $rawItem = sfOutputEscaper::unescape($item);
        if ($rawItem instanceof QubitInformationObject) {
            // Level of description
            if (null !== $levelOfDescription = QubitTerm::getById($item->levelOfDescriptionId)) {
                $node .= '<span class="levelOfDescription">'.render_value_inline($levelOfDescription->getName()).'</span>';
            }

            // Title
            $title = '';
            if ($item->identifier) {
                $title = $item->identifier.'&nbsp;-&nbsp;';
            }
            $title .= render_title($item);

            // Add link
            $node .= link_to($title, [$item, 'module' => 'informationobject'], ['title' => null]);

            // Publication status
            if ((null !== $status = $item->getPublicationStatus()) && QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $status->statusId) {
                $node .= '<span class="pubStatus">('.render_value_inline($status->__toString()).')</span>';
            }
        } elseif ($rawItem instanceof QubitTerm) {
            $action = isset($options['browser']) && true === $options['browser'] ? 'browseTerm' : 'index';

            // Add link
            $node .= link_to(render_title($item), [$item, 'module' => 'term', 'action' => $action]);
        }
    }

    // Close node tag
    $node .= '</li>';

    return $node;
}

function is_using_cli()
{
    return 'cli' === php_sapi_name();
}

function check_field_visibility($fieldName, $options = [])
{
    // Check always field if public option is set to true
    if (isset($options['public']) && $options['public']) {
        return sfConfig::get($fieldName, false);
    }

    return (is_using_cli() || sfContext::getInstance()->user->isAuthenticated()) || sfConfig::get($fieldName, false);
}

function get_search_i18n($hit, $fieldName, $options = [])
{
    // Return empty string by default or "Untitled" if allowEmpty is false
    $allowEmpty = true;
    if (isset($options['allowEmpty'])) {
        $allowEmpty = $options['allowEmpty'];
    }

    // Use culture fallback? Default = true
    $cultureFallback = true;
    if (isset($options['cultureFallback'])) {
        $cultureFallback = $options['cultureFallback'];
    }

    // Filter return value if empty
    $showUntitled = function () use ($allowEmpty) {
        if ($allowEmpty) {
            return '';
        }

        return sfContext::getInstance()->i18n->__('Untitled');
    };

    if (empty($hit)) {
        return $showUntitled();
    }

    if ($hit instanceof sfOutputEscaperObjectDecorator && 'Elastica\Result' == $hit->getClass()) {
        $hit = $hit->getData(); // type=sfOutputEscaperArrayDecorator
    }

    $accessField = function ($culture) use ($hit, $fieldName) {
        if (empty($hit['i18n'][$culture][$fieldName])) {
            return false;
        }

        return $hit['i18n'][$culture][$fieldName];
    };

    if (isset($options['culture'])) {
        $v = $accessField($options['culture']);
        if ($v) {
            return $v;
        }
    }

    $v = $accessField(sfContext::getInstance()->user->getCulture());
    if ($v) {
        return $v;
    }

    if ($cultureFallback) {
        $sourceCulture = is_object($hit) ? $hit->get('sourceCulture') : $hit['sourceCulture'];
        if (empty($sourceCulture)) {
            return $showUntitled();
        }

        $v = $accessField($sourceCulture);
        if (false !== $v) {
            return $v;
        }
    }

    return $showUntitled();
}

function get_search_creation_details($hit, $culture = null)
{
    if (!isset($culture)) {
        $culture = sfContext::getInstance()->user->getCulture();
    }

    if ($hit instanceof sfOutputEscaperObjectDecorator && 'Elastica\Result' == $hit->getClass()) {
        $hit = $hit->getData(); // type=sfOutputEscaperArrayDecorator
    }

    $details = [];

    // Get creators
    $creators = $hit['creators'];
    if (null !== $creators && 0 < count($creators)) {
        $details[] = get_search_i18n($creators[0], 'authorizedFormOfName', ['allowEmpty' => false, 'cultureFallback' => true]);
    }

    // WIP, we are not showing labels for now. See #5202.

    if (0 == count($details)) {
        return null;
    }

    return implode(', ', $details);
}

function render_autocomplete_string($hit)
{
    if ($hit instanceof sfOutputEscaperObjectDecorator && 'Elastica\Result' == $hit->getClass()) {
        $hit = $hit->getData(); // type=sfOutputEscaperArrayDecorator
    }

    $string = [];

    $levelOfDescriptionAndIdentifier = [];

    if (isset($hit['levelOfDescriptionId'])) {
        $levelOfDescriptionAndIdentifier[] = QubitTerm::getById($hit['levelOfDescriptionId'])->__toString();
    }

    if (
        '1' == sfConfig::get('app_inherit_code_informationobject', 1)
        && isset($hit['referenceCode']) && !empty($hit['referenceCode'])
    ) {
        $levelOfDescriptionAndIdentifier[] = $hit['referenceCode'];
    } elseif (isset($hit['identifier']) && !empty($hit['identifier'])) {
        $levelOfDescriptionAndIdentifier[] = $hit['identifier'];
    }

    if (0 < count($levelOfDescriptionAndIdentifier)) {
        $string[] = implode($levelOfDescriptionAndIdentifier, ' ');
    }

    $titleAndPublicationStatus = [];

    if (null !== $title = get_search_i18n($hit, 'title')) {
        $titleAndPublicationStatus[] = render_value_inline($title);
    }

    if (isset($hit['publicationStatusId']) && QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $hit['publicationStatusId']) {
        $titleAndPublicationStatus[] = '('.QubitTerm::getById($hit['publicationStatusId'])->__toString().')';
    }

    if (0 < count($titleAndPublicationStatus)) {
        $string[] = implode($titleAndPublicationStatus, ' ');
    }

    return implode(' - ', $string);
}

function escape_dc($text)
{
    return preg_replace('/\n/', '<lb/>', $text);
}

function render_search_result_date($date)
{
    $date = sfOutputEscaper::unescape($date);

    if (empty($date)) {
        return;
    }

    foreach ((array) $date as $item) {
        $displayDate = get_search_i18n($item, 'date');
        $startDate = isset($item['startDateString']) ? $item['startDateString'] : null;
        $endDate = isset($item['endDateString']) ? $item['endDateString'] : null;

        if (empty($displayDate) && empty($startDate) && empty($endDate)) {
            continue;
        }

        return Qubit::renderDateStartEnd($displayDate, $startDate, $endDate);
    }
}
