<?php

use org\bovigo\vfs\vfsStream;

/**
 * @internal
 *
 * @covers \settingsTask
 */
class settingsTaskTest extends \PHPUnit\Framework\TestCase
{
    protected $ormClasses;
    protected $vfs; // virtual filesystem
    protected $task;

    // Fixtures

    public function setUp(): void
    {
        $this->ormClasses = [
            'setting' => \AccessToMemory\test\mock\QubitSetting::class,
        ];

        // Add task to test with
        $this->task = new settingsTask(new sfEventDispatcher(), new sfFormatter());
        $this->task->setOrmClasses($this->ormClasses);

        // Wipe data that simulates settings database storage
        $this->ormClasses['setting']::wipe();

        // Add test settings
        $setting = new $this->ormClasses['setting']();
        $setting->name = 'siteTitle';
        $setting->setValue('My Site');
        $setting->save();

        $setting = new $this->ormClasses['setting']();
        $setting->name = 'informationobject';
        $setting->scope = 'ui_label';
        $setting->setValue('Archival description');
        $setting->save();

        $setting = new $this->ormClasses['setting']();
        $setting->name = 'informationobject';
        $setting->scope = 'bad_scope';
        $setting->setValue('Bad Scope Setting Value');
        $setting->save();

        $setting = new $this->ormClasses['setting']();
        $setting->name = 'settingWithTranslation';
        $setting->setValue('Français', ['culture' => 'fr']);
        $setting->save();

        // Define virtual file system
        $directory = [
            'title.txt' => 'My New Site',
            'setting.txt' => '',
            'unreadable.txt' => 'Some Setting',
        ];

        // Set up and cache the virtual file system
        $this->vfs = vfsStream::setup('root', null, $directory);

        // Make 'unreadable.txt' owned and readable only by root user
        $file = $this->vfs->getChild('root/unreadable.txt');
        $file->chmod('0400');
        $file->chown(vfsStream::OWNER_USER_1);
    }

    // Data providers

    public function getSettingValueProvider(): array
    {
        $inputs = [
            [
                'name' => 'siteTitle',
                'options' => [],
            ],
            [
                'name' => 'informationobject',
                'options' => ['scope' => 'ui_label'],
            ],
        ];

        $outputs = [
            [
                'type' => 'text',
                'value' => 'My Site',
            ],
            [
                'type' => 'text',
                'value' => 'Archival description',
            ],
        ];

        return [
            [$inputs[0], $outputs[0]],
            [$inputs[1], $outputs[1]],
        ];
    }

    public function setSettingValueProvider(): array
    {
        $inputs = [
            [
                'name' => 'siteTitle',
                'value' => 'Cool Site',
                'options' => [],
            ],
            [
                'name' => 'informationobject',
                'value' => 'Description',
                'options' => ['scope' => 'ui_label'],
            ],
        ];

        $outputs = [
            [
                'type' => 'text',
                'value' => 'Cool Site',
            ],
            [
                'type' => 'text',
                'value' => 'Description',
            ],
        ];

        return [
            [$inputs[0], $outputs[0]],
        ];
    }

    // Tests

    /**
     * @dataProvider getSettingValueProvider
     *
     * @param mixed $params
     * @param mixed $expected
     */
    public function testGetSettingValue($params, $expected): void
    {
        $value = $this->task->getSettingValue($params['name'], $params['options']);

        $this->assertSame($value, $expected['value']);
    }

    /**
     * @dataProvider setSettingValueProvider
     *
     * @param mixed $params
     * @param mixed $expected
     */
    public function testSetSettingValue($params, $expected): void
    {
        $this->task->setSettingValue($params['name'], $params['value'], $params['options']);

        $setting = $this->task->getSetting($params['name'], $params['options']);

        $this->assertSame($setting->getValue(), $expected['value']);
    }

    public function testGetSettingValueForNonexistent(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Setting does not exist.');

        $this->task->getSettingValue('this does not exist', []);
    }

    public function testValidateOptionsNoCulture(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Culture is invalid.');

        $arguments = ['operation' => 'get', 'name' => 'setting name'];
        $this->task->validateOptions($arguments, []);
    }

    public function testValidateOptionsBadCulture(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Culture is invalid.');

        $arguments = ['operation' => 'get', 'name' => 'setting name'];
        $this->task->validateOptions($arguments, ['culture' => 'invalid']);
    }

    public function testValidateOptionsValueUsedForWrongOperation(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The 'value' option must only be used with the 'set' operation.");

        $arguments = ['operation' => 'get', 'name' => 'setting name', 'value' => 'some value'];
        $this->task->validateOptions($arguments, ['culture' => 'en']);
    }

    public function testValidateOptionsFileUsedForWrongOperation(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The 'file' option must only be used with the 'get' or 'set' operations.");

        $arguments = ['operation' => 'list'];
        $this->task->validateOptions($arguments, ['file' => 'some value']);
    }

    public function testValidateOptionsFileUsedAtSameTimeAsValue(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The 'value' argument and 'file' option can't be used at the same time.");

        $arguments = ['operation' => 'set', 'name' => 'setting name', 'value' => 'some value'];
        $options = ['file' => 'some value', 'culture' => 'en'];
        $this->task->validateOptions($arguments, $options);
    }

    public function testValidateOptionsFileDoesNotExist(): void
    {
        $bogusFilePath = $this->vfs->url().'/bogus.txt';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("During a 'set' operation the 'file' option must refer to an existing file.");

        $arguments = ['operation' => 'set', 'name' => 'setting name'];
        $this->task->validateOptions($arguments, ['file' => $bogusFilePath, 'culture' => 'en']);
    }

    public function testValidateOptionsFileCanNotBeRead(): void
    {
        $unreadableFilePath = $this->vfs->url().'/unreadable.txt';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The 'file' option must refer to a readable file.");

        $arguments = ['operation' => 'set', 'name' => 'setting name'];
        $this->task->validateOptions($arguments, ['file' => $unreadableFilePath, 'culture' => 'en']);
    }

    public function testValidateOptionsFileAlreadyExists(): void
    {
        $existentFilePath = $this->vfs->url().'/title.txt';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("During a 'get' operation the 'file' option mustn't refer to an already existing file.");

        $arguments = ['operation' => 'get', 'name' => 'setting name'];
        $this->task->validateOptions($arguments, ['file' => $existentFilePath, 'culture' => 'en']);
    }

    public function testGetSettingValueWithNonDefaultLanguage(): void
    {
        $setting = $this->task->getSetting('settingWithTranslation');

        $this->assertSame($setting->getValue(['culture' => 'fr']), 'Français');
    }

    public function testSetSettingValueWithNonDefaultLanguage(): void
    {
        $options = ['culture' => 'es'];
        $setting = $this->task->setSettingValue('settingWithTranslation', 'Español', $options);

        $setting = $this->task->getSetting('settingWithTranslation');

        $this->assertSame($setting->getValue(['culture' => 'es']), 'Español');
        $this->assertSame($setting->getValue(['culture' => 'fr']), 'Français');
    }

    public function testSettingGetOperationValue(): void
    {
        $value = $this->task->getOperation('siteTitle', []);

        $this->assertSame($value, 'My Site');
    }

    public function testSettingGetOperationFile(): void
    {
        $toFilePath = $this->vfs->url().'/setting.txt';

        $options = ['file' => $toFilePath];
        $this->task->getOperation('siteTitle', $options);

        $toFileContents = file_get_contents($toFilePath);
        $this->assertSame($toFileContents, 'My Site');
    }

    public function testSettingSetOperationValue(): void
    {
        $this->task->setOperation('siteTitle', 'Another Title', []);

        $updatedValue = $this->task->getSettingValue('siteTitle', []);
        $this->assertSame($updatedValue, 'Another Title');
    }

    public function testSettingSetOperationFile(): void
    {
        $options = ['file' => $this->vfs->url().'/title.txt'];
        $this->task->setOperation('siteTitle', null, $options);

        $updatedValue = $this->task->getSettingValue('siteTitle', []);
        $this->assertSame($updatedValue, 'My New Site');
    }

    public function testSettingGetAll(): void
    {
        $settings = $this->task->getCurrentSettings();

        $this->assertSame($settings[0]['name'], 'informationobject');
        $this->assertSame($settings[0]['scope'], 'bad_scope');

        $this->assertSame($settings[1]['name'], 'informationobject');
        $this->assertSame($settings[1]['scope'], 'ui_label');

        $this->assertSame($settings[2]['name'], 'settingWithTranslation');
        $this->assertSame($settings[2]['scope'], null);

        $this->assertSame($settings[3]['name'], 'siteTitle');
        $this->assertSame($settings[3]['scope'], null);
    }

    public function testSettingInvalidOperation(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid operation.');

        $this->task->dispatchOperation(['operation' => 'invalid'], []);
    }

    public function testSettingsList(): void
    {
        $expected = "------------------------------------------\n"
                  ."Name                    Scope\n"
                  ."------------------------------------------\n"
                  ."informationobject       bad_scope\n"
                  ."informationobject       ui_label\n"
                  ."settingWithTranslation  \n"
                  ."siteTitle               \n";

        $output = $this->task->listOperation();

        $this->assertSame($output, $expected);
    }
}
