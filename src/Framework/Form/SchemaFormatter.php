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

class SchemaFormatter
{
    public $form;
    protected $rowFormat =
        "<div class=\"form-item\">\n%label%\n%error%%field%\n"
        ."%help%%hidden_fields%\n</div>\n";
    protected $helpFormat =
        "<div class=\"description\">\n%help%\n</div>\n";
    protected $errorRowFormat = '%errors%';
    protected $errorListFormatInARow =
        "<div class=\"messages error\"><ul>\n%errors%</ul></div>\n";
    protected $errorRowFormatInARow = "<li>%error%</li>\n";
    protected $namedErrorRowFormatInARow =
        "<li>%name%: %error%</li>\n";
    protected $decoratorFormat = '%content%';

    public function __construct(protected WidgetSchema $widgetSchema) {}

    public function formatRow(
        mixed $label,
        mixed $field,
        mixed $errors = [],
        mixed $help = '',
        mixed $hiddenFields = null,
    ) {
        return strtr($this->getRowFormat(), [
            '%label%' => (string) $label,
            '%field%' => (string) $field,
            '%error%' => $this->formatErrorsForRow($errors),
            '%help%' => $this->formatHelp($help),
            '%hidden_fields%' => null === $hiddenFields
                ? '%hidden_fields%'
                : (string) $hiddenFields,
        ]);
    }

    public function formatHelp(mixed $help)
    {
        return empty($help)
            ? ''
            : strtr($this->getHelpFormat(), [
                '%help%' => $this->translate((string) $help),
            ]);
    }

    public function formatErrorRow(mixed $errors)
    {
        return empty($errors)
            ? ''
            : strtr($this->getErrorRowFormat(), [
                '%errors%' => $this->formatErrorsForRow($errors),
            ]);
    }

    public function formatErrorsForRow(mixed $errors)
    {
        if (empty($errors)) {
            return '';
        }

        if ($errors instanceof ValidatorErrorSchema) {
            $errors = iterator_to_array($errors);
        } elseif (!is_array($errors)) {
            $errors = [$errors];
        }

        return strtr($this->getErrorListFormatInARow(), [
            '%errors%' => implode('', $this->flattenErrors($errors)),
        ]);
    }

    public function generateLabel(
        string $name,
        array $attributes = [],
    ) {
        $label = $this->generateLabelName($name);

        if (false === $label) {
            return '';
        }

        $attributes['for'] ??= $this->widgetSchema->generateId(
            $this->widgetSchema->generateName($name),
        );

        return $this->widgetSchema->renderContentTag(
            'label',
            $label,
            $attributes,
        );
    }

    public function generateLabelName(string $name)
    {
        $label = $this->widgetSchema->getLabel($name);

        if (null === $label || '' === $label) {
            $label = ucfirst(strtolower((string) preg_replace(
                '/[A-Z]/',
                ' $0',
                $name,
            )));
        }

        return false === $label
            ? false
            : $this->translate((string) $label);
    }

    public function setRowFormat(string $format): void
    {
        $this->rowFormat = $format;
    }

    public function getRowFormat()
    {
        return $this->rowFormat;
    }

    public function setErrorRowFormat(string $format): void
    {
        $this->errorRowFormat = $format;
    }

    public function getErrorRowFormat()
    {
        return $this->errorRowFormat;
    }

    public function setErrorListFormatInARow(string $format): void
    {
        $this->errorListFormatInARow = $format;
    }

    public function getErrorListFormatInARow()
    {
        return $this->errorListFormatInARow;
    }

    public function setErrorRowFormatInARow(string $format): void
    {
        $this->errorRowFormatInARow = $format;
    }

    public function getErrorRowFormatInARow()
    {
        return $this->errorRowFormatInARow;
    }

    public function setNamedErrorRowFormatInARow(string $format): void
    {
        $this->namedErrorRowFormatInARow = $format;
    }

    public function getNamedErrorRowFormatInARow()
    {
        return $this->namedErrorRowFormatInARow;
    }

    public function setDecoratorFormat(string $format): void
    {
        $this->decoratorFormat = $format;
    }

    public function getDecoratorFormat()
    {
        return $this->decoratorFormat;
    }

    public function setHelpFormat(string $format): void
    {
        $this->helpFormat = $format;
    }

    public function getHelpFormat()
    {
        return $this->helpFormat;
    }

    public function setWidgetSchema(WidgetSchema $widgetSchema): void
    {
        $this->widgetSchema = $widgetSchema;
    }

    public function getWidgetSchema(): WidgetSchema
    {
        return $this->widgetSchema;
    }

    protected function translate(
        string $subject,
        array $parameters = [],
    ): string {
        return function_exists('__')
            && \Atom\Framework\Bridge\Context::hasInstance()
            ? (string) __($subject, $parameters)
            : strtr($subject, $parameters);
    }

    private function flattenErrors(
        iterable $errors,
        string $prefix = '',
    ): array {
        $result = [];

        foreach ($errors as $name => $error) {
            if ($error instanceof ValidatorErrorSchema) {
                $result = array_merge(
                    $result,
                    $this->flattenErrors(
                        $error,
                        $prefix.(is_int($name) ? '' : $name.' > '),
                    ),
                );

                continue;
            }

            $message = $error instanceof ValidatorError
                ? $this->translate(
                    $error->getMessageFormat(),
                    $error->getArguments(),
                )
                : $this->translate((string) $error);
            $format = is_int($name)
                ? $this->getErrorRowFormatInARow()
                : $this->getNamedErrorRowFormatInARow();
            $result[] = strtr($format, [
                '%error%' => $message,
                '%name%' => $prefix.(is_int($name) ? '' : $name),
            ]);
        }

        return $result;
    }
}
