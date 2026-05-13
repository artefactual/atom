<?php

use AccessToMemory\test\TransactionTestCase;

/**
 * @internal
 *
 * @covers \BaseActorI18n::bulkSave
 * @covers \BaseSettingI18n::bulkSave
 */
class QubitI18nBulkSaveTest extends TransactionTestCase
{
    public function testNewTranslationsUseOneInsertAndRemainPersisted()
    {
        $setting = new QubitSetting();
        $setting->name = 'bulk_save_'.uniqid();
        $setting->sourceCulture = 'en';
        $setting->setValue('English', ['culture' => 'en']);
        $setting->setValue('Français', ['culture' => 'fr']);
        $setting->setValue('Español', ['culture' => 'es']);

        $insertsBefore = $this->getInsertCount();

        $this->assertSame($setting, $setting->save($this->connection));

        // One INSERT creates the setting and one creates all translations.
        $this->assertSame(2, $this->getInsertCount() - $insertsBefore);
        $this->assertSame(
            [
                'en' => 'English',
                'es' => 'Español',
                'fr' => 'Français',
            ],
            $this->getSettingTranslations($setting->id)
        );

        // Saving the now-clean objects must not insert duplicate translations.
        $insertsBefore = $this->getInsertCount();
        $setting->save($this->connection);
        $this->assertSame($insertsBefore, $this->getInsertCount());
    }

    public function testMixedNewAndExistingTranslationsUseSaveSemantics()
    {
        $setting = new QubitSetting();
        $setting->name = 'bulk_save_mixed_'.uniqid();
        $setting->sourceCulture = 'en';
        $setting->setValue('English', ['culture' => 'en']);
        $setting->setValue('Español', ['culture' => 'es']);
        $setting->save($this->connection);

        $setting->setValue('Revised English', ['culture' => 'en']);
        $setting->setValue('Deutsch', ['culture' => 'de']);

        $insertsBefore = $this->getInsertCount();
        $setting->save($this->connection);

        $this->assertSame(1, $this->getInsertCount() - $insertsBefore);
        $this->assertSame(
            [
                'de' => 'Deutsch',
                'en' => 'Revised English',
                'es' => 'Español',
            ],
            $this->getSettingTranslations($setting->id)
        );
    }

    public function testSparseTranslationsRetainTheirPopulatedColumns()
    {
        $actor = new QubitActor();
        $actor->indexOnSave = false;
        $actor->sourceCulture = 'en';
        $actor->setAuthorizedFormOfName('English name', ['culture' => 'en']);
        $actor->setHistory('Histoire française', ['culture' => 'fr']);
        $actor->save($this->connection);

        $statement = $this->connection->prepare(
            'SELECT culture, authorized_form_of_name, history
            FROM actor_i18n
            WHERE id = ?
            ORDER BY culture'
        );
        $statement->execute([$actor->id]);

        $this->assertSame(
            [
                [
                    'culture' => 'en',
                    'authorized_form_of_name' => 'English name',
                    'history' => null,
                ],
                [
                    'culture' => 'fr',
                    'authorized_form_of_name' => null,
                    'history' => 'Histoire française',
                ],
            ],
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function testFailedBulkInsertRetainsPropelExceptionContract()
    {
        $actor = new QubitActor();
        $actor->indexOnSave = false;
        $actor->sourceCulture = 'en';
        $actor->setAuthorizedFormOfName(
            str_repeat('x', 1025),
            ['culture' => 'en']
        );

        try {
            $actor->save($this->connection);
            $this->fail('Expected an oversized translation to fail.');
        } catch (PropelException $exception) {
            $this->assertStringStartsWith(
                'Unable to execute INSERT statement.',
                $exception->getMessage()
            );
            $this->assertInstanceOf(PDOException::class, $exception->getCause());
        }
    }

    private function getInsertCount()
    {
        $statement = $this->connection->query(
            "SHOW SESSION STATUS LIKE 'Com_insert'"
        );
        $status = $statement->fetch(PDO::FETCH_NUM);

        return (int) $status[1];
    }

    private function getSettingTranslations($id)
    {
        $statement = $this->connection->prepare(
            'SELECT culture, value
            FROM setting_i18n
            WHERE id = ?
            ORDER BY culture'
        );
        $statement->execute([$id]);

        return $statement->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
