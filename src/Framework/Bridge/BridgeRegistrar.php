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

namespace Atom\Framework\Bridge;

use Atom\Framework\Console\CommandArgument;
use Atom\Framework\Console\CommandOption;
use Atom\Framework\Console\Formatter;
use Atom\Framework\Console\Task;
use Atom\Framework\Database\DatabaseManager;
use Atom\Framework\Form\AndValidator;
use Atom\Framework\Form\BooleanValidator;
use Atom\Framework\Form\CallbackValidator;
use Atom\Framework\Form\ChoiceValidator;
use Atom\Framework\Form\ChoiceWidget;
use Atom\Framework\Form\CsrfTokenValidator;
use Atom\Framework\Form\DateValidator;
use Atom\Framework\Form\EmailValidator;
use Atom\Framework\Form\FileValidator;
use Atom\Framework\Form\Form;
use Atom\Framework\Form\FormField;
use Atom\Framework\Form\I18nChoiceCountryValidator;
use Atom\Framework\Form\I18nChoiceCountryWidget;
use Atom\Framework\Form\I18nChoiceLanguageValidator;
use Atom\Framework\Form\I18nChoiceLanguageWidget;
use Atom\Framework\Form\InputCheckboxWidget;
use Atom\Framework\Form\InputFileWidget;
use Atom\Framework\Form\InputHiddenWidget;
use Atom\Framework\Form\InputPasswordWidget;
use Atom\Framework\Form\InputWidget;
use Atom\Framework\Form\IntegerValidator;
use Atom\Framework\Form\NumberValidator;
use Atom\Framework\Form\OrValidator;
use Atom\Framework\Form\PassValidator;
use Atom\Framework\Form\RegexValidator;
use Atom\Framework\Form\SchemaCompareValidator;
use Atom\Framework\Form\SchemaFormatter;
use Atom\Framework\Form\SchemaValidator;
use Atom\Framework\Form\SelectManyWidget;
use Atom\Framework\Form\SelectRadioWidget;
use Atom\Framework\Form\SelectWidget;
use Atom\Framework\Form\StringValidator;
use Atom\Framework\Form\TextareaWidget;
use Atom\Framework\Form\UrlValidator;
use Atom\Framework\Form\ValidatedFile;
use Atom\Framework\Form\Validator;
use Atom\Framework\Form\ValidatorError;
use Atom\Framework\Form\ValidatorErrorSchema;
use Atom\Framework\Form\Widget;
use Atom\Framework\Form\WidgetSchema;

final class BridgeRegistrar
{
    private const ALIASES = [
        [Configuration::class, 'sfConfig'],
        [ParameterHolder::class, 'sfParameterHolder'],
        [View::class, 'sfView'],
        [Event::class, 'sfEvent'],
        [EventDispatcher::class, 'sfEventDispatcher'],
        [Context::class, 'sfContext'],
        [CultureInfo::class, 'sfCultureInfo'],
        [DateFormatter::class, 'sfDateFormat'],
        [NumberFormatter::class, 'sfNumberFormat'],
        [CommandArgument::class, 'sfCommandArgument'],
        [CommandOption::class, 'sfCommandOption'],
        [Formatter::class, 'sfFormatter'],
        [Formatter::class, 'sfAnsiColorFormatter'],
        [Task::class, 'sfTask'],
        [Task::class, 'sfCommandApplicationTask'],
        [Task::class, 'sfBaseTask'],
        [DatabaseManager::class, 'sfDatabaseManager'],
        [RuntimeConfiguration::class, 'sfProjectConfiguration'],
        [RuntimeConfiguration::class, 'sfApplicationConfiguration'],
        [RuntimeConfiguration::class, 'ProjectConfiguration'],
        [Form::class, 'sfForm'],
        [FormField::class, 'sfFormField'],
        [FormField::class, 'sfFormFieldSchema'],
        [Widget::class, 'sfWidgetForm'],
        [WidgetSchema::class, 'sfWidgetFormSchema'],
        [SchemaFormatter::class, 'sfWidgetFormSchemaFormatter'],
        [InputWidget::class, 'sfWidgetFormInput'],
        [InputCheckboxWidget::class, 'sfWidgetFormInputCheckbox'],
        [InputFileWidget::class, 'sfWidgetFormInputFile'],
        [InputHiddenWidget::class, 'sfWidgetFormInputHidden'],
        [InputPasswordWidget::class, 'sfWidgetFormInputPassword'],
        [ChoiceWidget::class, 'sfWidgetFormChoice'],
        [SelectWidget::class, 'sfWidgetFormSelect'],
        [SelectManyWidget::class, 'sfWidgetFormSelectMany'],
        [SelectRadioWidget::class, 'sfWidgetFormSelectRadio'],
        [TextareaWidget::class, 'sfWidgetFormTextarea'],
        [
            I18nChoiceCountryWidget::class,
            'sfWidgetFormI18nChoiceCountry',
        ],
        [
            I18nChoiceLanguageWidget::class,
            'sfWidgetFormI18nChoiceLanguage',
        ],
        [Validator::class, 'sfValidatorBase'],
        [ValidatorError::class, 'sfValidatorError'],
        [ValidatorErrorSchema::class, 'sfValidatorErrorSchema'],
        [SchemaValidator::class, 'sfValidatorSchema'],
        [StringValidator::class, 'sfValidatorString'],
        [IntegerValidator::class, 'sfValidatorInteger'],
        [NumberValidator::class, 'sfValidatorNumber'],
        [BooleanValidator::class, 'sfValidatorBoolean'],
        [ChoiceValidator::class, 'sfValidatorChoice'],
        [EmailValidator::class, 'sfValidatorEmail'],
        [UrlValidator::class, 'sfValidatorUrl'],
        [RegexValidator::class, 'sfValidatorRegex'],
        [PassValidator::class, 'sfValidatorPass'],
        [DateValidator::class, 'sfValidatorDate'],
        [CallbackValidator::class, 'sfValidatorCallback'],
        [FileValidator::class, 'sfValidatorFile'],
        [
            I18nChoiceCountryValidator::class,
            'sfValidatorI18nChoiceCountry',
        ],
        [
            I18nChoiceLanguageValidator::class,
            'sfValidatorI18nChoiceLanguage',
        ],
        [AndValidator::class, 'sfValidatorAnd'],
        [OrValidator::class, 'sfValidatorOr'],
        [SchemaCompareValidator::class, 'sfValidatorSchemaCompare'],
        [CsrfTokenValidator::class, 'sfValidatorCSRFToken'],
        [ValidatedFile::class, 'sfValidatedFile'],
        [OutputEscaper::class, 'sfOutputEscaper'],
        [SafeValue::class, 'sfOutputEscaperSafe'],
        [Component::class, 'sfComponent'],
        [Action::class, 'sfAction'],
        [Actions::class, 'sfActions'],
        [User::class, 'sfUser'],
        [User::class, 'sfBasicSecurityUser'],
        [PropelBridge::class, 'sfPropel'],
        [BridgeException::class, 'sfException'],
        [BridgeException::class, 'sfConfigurationException'],
        [BridgeException::class, 'sfControllerException'],
        [BridgeException::class, 'sfInitializationException'],
        [NotFoundException::class, 'sfError404Exception'],
        [StopException::class, 'sfStopException'],
        [ForwardException::class, 'sfForwardException'],
    ];

    private static bool $registered = false;

    public function register(): void
    {
        if (self::$registered) {
            return;
        }

        require_once dirname(__DIR__).'/Form/Validators.php';

        require_once dirname(__DIR__).'/Form/Widgets.php';

        foreach (self::ALIASES as [$class, $alias]) {
            if (class_exists($alias, false)) {
                if (!is_a($alias, $class, true)) {
                    throw new BridgeException(sprintf(
                        'Cannot boot the AtoM bridge after loading "%s".',
                        $alias,
                    ));
                }

                continue;
            }

            class_alias($class, $alias);
        }

        require_once __DIR__.'/functions.php';
        self::$registered = true;
    }
}
