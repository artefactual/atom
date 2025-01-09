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

function render_field($field, $resource = null, array $options = [])
{
    $options += ['name' => $field->getName()];

    $div = null;
    $culture = sfContext::getInstance()->user->getCulture();

    $resourceRaw = sfOutputEscaper::unescape($resource);
    if (isset($resourceRaw) && $culture != $resourceRaw->sourceCulture) {
        if ($resourceRaw instanceof QubitSetting) {
            $options['name'] = 'value';
        }

        try {
            if ($resourceRaw instanceof sfRadPlugin || $resourceRaw instanceof arDacsPlugin) {
                $source = $resourceRaw->getProperty($options['name'], ['sourceCulture' => true]);
                $fallback = $resourceRaw->getProperty($options['name']);
            } else {
                $source = $resourceRaw->__get($options['name'], ['sourceCulture' => true]);
                $fallback = $resourceRaw->__get($options['name']);
            }
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

    if (sfConfig::get('app_b5_theme', false)) {
        return render_b5_field($field, $div, $options);
    }

    if (isset($options['onlyInput']) && $options['onlyInput']) {
        $field = $div.$field->render($options);
    } else {
        $field = '<div class="form-item">'.$field->renderLabel().$field->renderError()
            .$div.$field->render($options).$field->renderHelp().'</div>';
    }

    return $field;
}

function render_b5_field($field, $translation = null, $options = [])
{
    $isFormCheck = false;
    $inputClass = 'form-control';
    $labelClass = 'form-label';

    // TODO: this should be the field id
    $name = $field->getName();
    $widget = $field->getWidget();

    if (
        in_array($widget->getOption('type'), ['checkbox', 'radio'])
        || $widget instanceof sfWidgetFormSelectRadio
        || (
            $widget instanceof sfWidgetFormChoice
            && !$widget instanceof sfWidgetFormI18nChoiceLanguage
            && !$widget instanceof sfWidgetFormI18nChoiceCountry
        )
    ) {
        $isFormCheck = true;
        $inputClass = 'form-check-input';
        $labelClass = 'form-check-label';
    } else if ('color' == $widget->getOption('type')) {
        $inputClass .= ' form-control-color';
    }

    if (
        (
            empty($options['class'])
            || false === strpos($options['class'], 'form-autocomplete')
        )
        && (
            $widget instanceof sfWidgetFormSelect
            || $widget instanceof sfWidgetFormI18nChoiceLanguage
            || $widget instanceof sfWidgetFormI18nChoiceCountry
        )
    ) {
        $inputClass = 'form-select';
    }

    if (empty($options['class'])) {
        $options['class'] = $inputClass;
    } else {
        $options['class'] .= ' '.$inputClass;
    }

    if ($field->hasError()) {
        $options['class'] .= ' is-invalid';
        $options['aria-invalid'] = 'true';
        if (isset($options['aria-describedby'])) {
            $options['aria-describedby'] .= ' '.$name.'-errors';
        } else {
            $options['aria-describedby'] = $name.'-errors';
        }
    }

    if (isset($translation)) {
        if (isset($options['aria-describedby'])) {
            $options['aria-describedby'] .= ' '.$name.'-translation';
        } else {
            $options['aria-describedby'] = $name.'-translation';
        }
    }

    // Autocomplete extra inputs
    $extraInputs = '';
    if (isset($options['extraInputs'])) {
        $extraInputs = $options['extraInputs'];
        unset($options['extraInputs']);
    }

    if (isset($options['onlyInputs']) && $options['onlyInputs']) {
        unset($options['onlyInputs']);

        return $translation
            .$field->render($options)
            .$extraInputs
            .$field->renderError();
    }

    // We need to render the label first to set the input name in
    // arB5WidgetFormSchemaFormatter as it's used for the help id.
    $label = $field->renderLabel(null, ['class' => $labelClass]);
    $help = $field->renderHelp();
    if (!empty($help)) {
        if (isset($options['aria-describedby'])) {
            $options['aria-describedby'] .= ' '.$name.'-help';
        } else {
            $options['aria-describedby'] = $name.'-help';
        }
    }

    if ($isFormCheck) {
        // Special case for grouped radio buttons
        if (
          $widget instanceof sfWidgetFormChoice
          || $widget instanceof sfWidgetFormSelectRadio
        ) {
            return '<div class="mb-3">'
                .'<fieldset'
                .(isset($options['aria-describedby'])
                    ? ' aria-describedby="'.$options['aria-describedby'].'"'
                    : '')
                .'><legend class="fs-6">'
                .$field->renderLabelName()
                .'</legend>'
                .$field->render(['class' => 'form-check-input'])
                .'</fieldset>'
                .$field->renderError()
                .$help
                .'</div>';
        }

        return '<div class="form-check mb-3">'
            .$field->render($options)
            .$label
            .$field->renderError()
            .$help
            .'</div>';
    }

    return '<div class="mb-3">'
        .$label
        .$translation
        .$field->render($options)
        .$extraInputs
        .$field->renderError()
        .$help
        .'</div>';
}

function render_show($label, $value, $options = [])
{
    if (sfConfig::get('app_b5_theme', false)) {
        return render_b5_show($label, $value, $options);
    }

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

function render_b5_show_field_css_classes($options = [])
{
    return 'row g-0';
}

function render_b5_show_subfield_css_classes($options = [])
{
    return 'd-flex flex-wrap';
}

function render_b5_show($label, $value, $options = [])
{
    $tag = 'div';
    $cssClasses = 'field text-break';
    if (isset($options['fieldClass'])) {
        $cssClasses .= ' '.$options['fieldClass'];
    }
    if (!isset($options['isSubField'])) {
        $cssClasses .= ' '.render_b5_show_field_css_classes($options);
    } else {
        $cssClasses .= ' '.render_b5_show_subfield_css_classes($options);
    }

    $labelContainer = render_b5_show_label($label, $options);
    $valuecontainer = render_b5_show_value($value, $options);

    return render_b5_show_container(
        $tag, $labelContainer.$valuecontainer, $cssClasses, $options
    );
}

function render_b5_show_container($tag, $content, $cssClasses = '', $options = [])
{
    $cssClass = $cssClasses ? ' class="'.$cssClasses.'"' : '';

    return "<{$tag}{$cssClass}>{$content}</{$tag}>";
}

function render_b5_show_label_css_classes($options = [])
{
    $result = 'h6 lh-base m-0 text-muted';
    if (!isset($options['isSubField'])) {
        $result .= ' col-3 border-end text-end p-2';
    } else {
        $result .= ' me-2';
    }

    return $result;
}

function render_b5_show_label($label, $options = [])
{
    $tag = isset($options['isSubField']) ? 'h4' : 'h3';
    $cssClasses = render_b5_show_label_css_classes($options);
    if (isset($options['labelClass'])) {
        $cssClasses .= ' '.$options['labelClass'];
    }

    return render_b5_show_container($tag, $label, $cssClasses, $options);
}

function render_b5_show_value_css_classes($options = [])
{
    return isset($options['isSubField']) ? '' : 'col-9 p-2';
}

function render_b5_show_value($value, $options = [])
{
    $tag = 'div';
    $cssClasses = render_b5_show_value_css_classes($options);
    if (isset($options['valueClass'])) {
        $cssClasses .= ' '.$options['valueClass'];
    }

    $finalValue = $value;
    if (is_array($value) || $value instanceof sfOutputEscaperObjectDecorator || $value instanceof sfOutputEscaperArrayDecorator) {
        $finalValue = '<ul class="'.render_b5_show_list_css_classes().'">';
        foreach ($value as $item) {
            $finalValue .= '<li>'.$item.'</li>';
        }
        $finalValue .= '</ul>';
    }

    return render_b5_show_container($tag, $finalValue, $cssClasses, $options);
}

function render_b5_section_heading(
    $text,
    $condition = false,
    $url = null,
    $linkOptions = []
) {
    if ($condition) {
        $linkClasses = 'text-primary text-decoration-none';
        $linkOptions['class'] = $linkOptions['class']
            ? $linkOptions['class'].' '.$linkClasses
            : $linkClasses;
        $linkOptions['title'] = $linkOptions['title'] ?: __('Edit').' '.$text;
        $content = link_to($text, $url, $linkOptions);
    } else {
        $content = render_b5_show_container(
            'div',
            $text,
            'd-flex p-3 border-bottom text-primary'
        );
    }

    return render_b5_show_container(
        'h2',
        $content,
        'h5 mb-0 atom-section-header'
    );
}

function render_b5_show_list_css_classes($options = [])
{
    return 'm-0 ms-1 ps-3';
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

    return round($val / pow(1024, $i), 1).' '.$units[$i];
}

function render_treeview_node($item, array $classes = [], array $options = [])
{
    if (sfConfig::get('app_b5_theme', false)) {
        return render_b5_treeview_node($item, $classes, $options);
    }

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
            $node .= ' data-title="'.strip_tags(implode(' - ', $dataTitle)).'"';
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

function render_b5_treeview_node($item, array $classes = [], array $options = [])
{
    // Build array of classes
    $_classes = ['list-group-item'];
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
            $node .= ' data-title="'.strip_tags(implode(' - ', $dataTitle)).'"';
        }
    } elseif ($item instanceof QubitTerm) {
        $node .= ' data-title="'.esc_entities(sfConfig::get('app_ui_label_term')).'"';
    }

    $node .= ' data-content="'.strip_markdown($item).'"';

    // Close tag
    $node .= '>';

    // Add <i> tag if the node is expandable
    if (isset($_classes['expand']) || isset($_classes['ancestor'])) {
        $node .= '<i class="arrow" aria-hidden="true"></i>';
    }

    $node .= '<span class="text text-truncate">';

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
                $node .= '<span class="me-1 text-dark">'.render_value_inline($levelOfDescription->getName()).'</span>';
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
                $node .= '<span class="ms-1 text-muted">('.render_value_inline($status->__toString()).')</span>';
            }
        } elseif ($rawItem instanceof QubitTerm) {
            $action = isset($options['browser']) && true === $options['browser'] ? 'browseTerm' : 'index';

            // Add link
            $node .= link_to(render_title($item), [$item, 'module' => 'term', 'action' => $action]);
        }
    }

    $node .= '</span></li>';

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
