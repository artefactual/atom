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

namespace Atom\Tests\Framework\Form;

use Atom\Framework\Bridge\BridgeRegistrar;
use Atom\Framework\Bridge\Configuration;
use Atom\Framework\Form\Form;
use Atom\Framework\Form\InputWidget;
use Atom\Framework\Form\StringValidator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Atom\Framework\Form\Form
 * @covers \Atom\Framework\Form\FormField
 * @covers \Atom\Framework\Form\SchemaValidator
 * @covers \Atom\Framework\Form\Validator
 * @covers \Atom\Framework\Form\Widget
 * @covers \Atom\Framework\Form\WidgetSchema
 */
final class FormTest extends TestCase
{
    protected function setUp(): void
    {
        (new BridgeRegistrar())->register();
        Configuration::set('sf_csrf_secret', 'test-secret');
    }

    protected function tearDown(): void
    {
        Configuration::clear();
    }

    public function testAliasesUseTheOwnedFormRuntime(): void
    {
        self::assertTrue(class_exists('sfForm'));
        self::assertTrue(is_a('sfForm', Form::class, true));
        self::assertStringContainsString(
            '/src/Framework/Form/Form.php',
            (new \ReflectionClass('sfForm'))->getFileName(),
        );
    }

    public function testBindsAndCleansAProtectedForm(): void
    {
        $form = new \sfForm();
        $form->setWidget('email', new \sfWidgetFormInput());
        $form->setValidator(
            'email',
            new \sfValidatorEmail(['required' => true]),
        );
        $form->bind([
            'email' => 'person@example.org',
            $form->getCSRFFieldName() => $form->getDefault(
                $form->getCSRFFieldName(),
            ),
        ]);

        self::assertTrue($form->isValid());
        self::assertSame('person@example.org', $form->getValue('email'));
        self::assertArrayNotHasKey(
            $form->getCSRFFieldName(),
            $form->getValues(),
        );
    }

    public function testReportsFieldAndCsrfErrors(): void
    {
        $form = new \sfForm();
        $form->setWidget('email', new \sfWidgetFormInput());
        $form->setValidator(
            'email',
            new \sfValidatorEmail(['required' => true]),
        );
        $form->bind([
            'email' => 'not-an-email',
            $form->getCSRFFieldName() => 'invalid',
        ]);

        self::assertFalse($form->isValid());
        self::assertTrue($form->email->hasError());
        self::assertTrue($form->hasGlobalErrors());
        self::assertStringContainsString(
            'CSRF attack detected',
            $form->renderGlobalErrors(),
        );
    }

    public function testRendersLegacyNamesAndMethodOverride(): void
    {
        $form = new \sfForm([], [], false);
        $form->getWidgetSchema()->setNameFormat('account[%s]');
        $form->setWidget(
            'username',
            new \sfWidgetFormInput([], ['autocomplete' => 'username']),
        );
        $form->setValidator(
            'username',
            new \sfValidatorString(['required' => true]),
        );

        self::assertStringContainsString(
            'name="account[username]"',
            $form->username->render(),
        );
        self::assertStringContainsString(
            'name="sf_method" value="delete"',
            $form->renderFormTag('/account', ['method' => 'delete']),
        );
    }

    public function testEmbedsAndValidatesNestedForms(): void
    {
        $child = new \sfForm([], [], false);
        $child->setWidget('title', new \sfWidgetFormInput());
        $child->setValidator(
            'title',
            new \sfValidatorString(['required' => true]),
        );
        $form = new \sfForm([], [], false);
        $form->getWidgetSchema()->setNameFormat('record[%s]');
        $form->embedForm('metadata', $child);
        $form->bind(['metadata' => ['title' => 'Example']]);

        self::assertTrue($form->isValid());
        self::assertSame(
            ['title' => 'Example'],
            $form->getValue('metadata'),
        );
        self::assertStringContainsString(
            'name="record[metadata][title]"',
            $form['metadata']['title']->render(),
        );
    }

    public function testSupportsNativeBridgeTypesDirectly(): void
    {
        $form = new Form([], [], false);
        $form->setWidget('title', new InputWidget());
        $form->setValidator(
            'title',
            new StringValidator(['required' => true]),
        );
        $form->bind(['title' => 'An archive']);

        self::assertTrue($form->isValid());
        self::assertSame('An archive', $form->getValue('title'));
    }
}
