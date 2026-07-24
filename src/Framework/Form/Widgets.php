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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM). If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Atom\Framework\Form;

use Atom\Framework\Bridge\Configuration;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Languages;

class InputWidget extends Widget
{
    protected function configure(
        array $options = [],
        array $attributes = [],
    ) {
        $this->addOption('type', 'text');
    }
}

class InputCheckboxWidget extends InputWidget
{
    public function render(
        string $name,
        mixed $value = null,
        array $attributes = [],
        array|ValidatorError|null $errors = [],
    ) {
        $checkValue = $this->getOption('value_attribute_value');
        $attributes = array_replace(
            ['type' => 'checkbox', 'name' => $name, 'value' => $checkValue],
            $this->attributes,
            $attributes,
        );

        if (null !== $value && false !== $value) {
            $attributes['checked'] = true;
        }

        return $this->renderTag('input', $attributes);
    }

    protected function configure(
        array $options = [],
        array $attributes = [],
    ) {
        parent::configure($options, $attributes);
        $this->setOption('type', 'checkbox');
        $this->addOption('value_attribute_value');
    }
}

class InputFileWidget extends InputWidget
{
    public function render(
        string $name,
        mixed $value = null,
        array $attributes = [],
        array|ValidatorError|null $errors = [],
    ) {
        return $this->renderTag('input', array_replace(
            ['type' => 'file', 'name' => $name],
            $this->attributes,
            $attributes,
        ));
    }

    protected function configure(
        array $options = [],
        array $attributes = [],
    ) {
        parent::configure($options, $attributes);
        $this->setOption('type', 'file');
        $this->setOption('needs_multipart', true);
    }
}

class InputHiddenWidget extends InputWidget
{
    protected function configure(
        array $options = [],
        array $attributes = [],
    ) {
        parent::configure($options, $attributes);
        $this->setOption('is_hidden', true);
        $this->setOption('type', 'hidden');
    }
}

class InputPasswordWidget extends InputWidget
{
    public function render(
        string $name,
        mixed $value = null,
        array $attributes = [],
        array|ValidatorError|null $errors = [],
    ) {
        return parent::render(
            $name,
            $this->getOption('always_render_empty') ? null : $value,
            $attributes,
            $errors,
        );
    }

    protected function configure(
        array $options = [],
        array $attributes = [],
    ) {
        parent::configure($options, $attributes);
        $this->addOption('always_render_empty', true);
        $this->setOption('type', 'password');
    }
}

class SelectWidget extends Widget
{
    public function getChoices(): array
    {
        return $this->choices();
    }

    public function render(
        string $name,
        mixed $value = null,
        array $attributes = [],
        array|ValidatorError|null $errors = [],
    ) {
        $multiple = (bool) $this->getOption('multiple');
        $values = array_map('strval', $multiple ? (array) $value : [$value]);
        $attributes = array_replace(
            ['name' => $multiple ? $name.'[]' : $name],
            $this->attributes,
            $attributes,
        );

        if ($multiple) {
            $attributes['multiple'] = true;
        }

        return $this->renderContentTag(
            'select',
            $this->renderChoices($this->choices(), $values),
            $attributes,
        );
    }

    protected function configure(
        array $options = [],
        array $attributes = [],
    ) {
        $this->addRequiredOption('choices');
        $this->addOption('multiple', false);
    }

    protected function choices(): array
    {
        $choices = $this->getOption('choices');

        if (is_callable($choices)) {
            $choices = $choices();
        }

        return (array) $choices;
    }

    private function renderChoices(array $choices, array $values): string
    {
        $html = '';

        foreach ($choices as $key => $label) {
            if (is_array($label)) {
                $html .= $this->renderContentTag(
                    'optgroup',
                    $this->renderChoices($label, $values),
                    ['label' => $key],
                );

                continue;
            }

            $attributes = ['value' => $key];

            if (in_array((string) $key, $values, true)) {
                $attributes['selected'] = true;
            }

            $html .= $this->renderContentTag(
                'option',
                htmlspecialchars(
                    (string) $label,
                    \ENT_QUOTES | \ENT_SUBSTITUTE,
                    'UTF-8',
                ),
                $attributes,
            );
        }

        return $html;
    }
}

class SelectManyWidget extends SelectWidget
{
    protected function configure(
        array $options = [],
        array $attributes = [],
    ) {
        parent::configure($options, $attributes);
        $this->setOption('multiple', true);
    }
}

class SelectRadioWidget extends SelectWidget
{
    public function render(
        string $name,
        mixed $value = null,
        array $attributes = [],
        array|ValidatorError|null $errors = [],
    ) {
        $inputs = [];
        $idName = str_ends_with($name, '[]') ? $name : $name.'[]';
        $inputName = substr($idName, 0, -2);
        $bootstrap = (bool) Configuration::get('app_b5_theme', false);

        foreach ($this->choices() as $key => $label) {
            $id = $this->generateId($idName, $key);
            $inputAttributes = array_replace(
                [
                    'type' => 'radio',
                    'name' => $inputName,
                    'value' => $key,
                    'id' => $id,
                ],
                $this->attributes,
                $attributes,
            );

            if ($bootstrap) {
                $inputAttributes['class'] = 'form-check-input';
            }

            if ((string) $key === (string) $value) {
                $inputAttributes['checked'] = true;
            }

            $input = $this->renderTag('input', $inputAttributes);
            $inputs[(string) $id] = [
                'input' => $input,
                'label' => $this->renderContentTag(
                    'label',
                    (string) $label,
                    array_filter([
                        'for' => $id,
                        'class' => $bootstrap
                            ? 'form-check-label'
                            : null,
                    ]),
                ),
            ];
        }

        return ($this->getOption('formatter'))($this, $inputs);
    }

    public function formatter(self $widget, array $inputs)
    {
        $rows = array_map(
            fn (array $input): string => $input['input']
                .$this->getOption('label_separator').$input['label'],
            $inputs,
        );

        if ((bool) Configuration::get('app_b5_theme', false)) {
            return implode(
                $this->getOption('separator'),
                array_map(
                    static fn (string $row): string => sprintf(
                        '<div class="form-check">%s</div>',
                        $row,
                    ),
                    $rows,
                ),
            );
        }

        return $this->renderContentTag(
            'ul',
            implode(
                $this->getOption('separator'),
                array_map(
                    fn (string $row): string => '<li>'.$row.'</li>',
                    $rows,
                ),
            ),
            ['class' => $this->getOption('class')],
        );
    }

    protected function configure(
        array $options = [],
        array $attributes = [],
    ) {
        parent::configure($options, $attributes);
        $this->addOption('class', 'radio_list');
        $this->addOption('label_separator', '&nbsp;');
        $this->addOption('separator', "\n");
        $this->addOption('formatter', [$this, 'formatter']);
        $this->addOption('template', '%group% %options%');
    }
}

class ChoiceWidget extends SelectWidget
{
    public function render(
        string $name,
        mixed $value = null,
        array $attributes = [],
        array|ValidatorError|null $errors = [],
    ): string {
        if (!$this->getOption('expanded')) {
            return parent::render($name, $value, $attributes, $errors);
        }

        if (!$this->getOption('multiple')) {
            $radio = new SelectRadioWidget(
                ['choices' => $this->choices()],
                $this->attributes,
            );

            return $radio->render($name, $value, $attributes, $errors);
        }

        $html = '';

        foreach ($this->choices() as $key => $label) {
            $widget = new InputCheckboxWidget(
                ['value_attribute_value' => $key],
                $this->attributes,
            );
            $html .= $widget->render(
                $name.'[]',
                in_array((string) $key, array_map('strval', (array) $value)),
                $attributes,
            ).$this->renderContentTag('label', $label);
        }

        return $html;
    }

    protected function configure(
        array $options = [],
        array $attributes = [],
    ): void {
        parent::configure($options, $attributes);
        $this->addOption('expanded', false);
    }
}

class TextareaWidget extends Widget
{
    public function render(
        string $name,
        mixed $value = null,
        array $attributes = [],
        array|ValidatorError|null $errors = [],
    ): string {
        return $this->renderContentTag(
            'textarea',
            htmlspecialchars(
                (string) $value,
                \ENT_QUOTES | \ENT_SUBSTITUTE,
                'UTF-8',
            ),
            array_replace(
                ['name' => $name],
                $this->attributes,
                $attributes,
            ),
        );
    }
}

class I18nChoiceCountryWidget extends SelectWidget
{
    protected function configure(
        array $options = [],
        array $attributes = [],
    ): void {
        $this->addOption('culture', 'en');
        $this->addOption('add_empty', false);
        $culture = (string) ($options['culture'] ?? 'en');
        $choices = Countries::getNames($culture);

        if ($options['add_empty'] ?? false) {
            $choices = ['' => ''] + $choices;
        }

        $this->addOption('choices', $choices);
        parent::configure($options, $attributes);
    }
}

class I18nChoiceLanguageWidget extends SelectWidget
{
    protected function configure(
        array $options = [],
        array $attributes = [],
    ): void {
        $this->addOption('culture', 'en');
        $this->addOption('add_empty', false);
        $culture = (string) ($options['culture'] ?? 'en');
        $choices = Languages::getNames($culture);

        if ($options['add_empty'] ?? false) {
            $choices = ['' => ''] + $choices;
        }

        $this->addOption('choices', $choices);
        parent::configure($options, $attributes);
    }
}
