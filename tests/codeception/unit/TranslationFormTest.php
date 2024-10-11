<?php

namespace humhub\modules\translation\tests\codeception\unit;

use humhub\modules\space\models\Space;
use humhub\modules\translation\models\forms\TranslationForm;
use humhub\modules\translation\models\Languages;
use humhub\modules\translation\models\TranslationLog;
use translation\TranslationTest;

class TranslationFormTest extends TranslationTest
{
    public function testModuleIdSelection()
    {
        $form = new TranslationForm();
        $form->load([ ]);
        $moduleIdSelection = $form->getModuleIdSelection();

        $this->assertTrue(array_key_exists('core', $moduleIdSelection));
        $this->assertStringContainsString('HumHub - core (', $moduleIdSelection['core']);
        $this->assertStringContainsString('HumHub - activity (', $moduleIdSelection['activity']);
        $this->assertStringContainsString('Module - translation (', $moduleIdSelection['translation']);
    }

    public function testLoadInitAsAdmin()
    {
        $form = new TranslationForm();
        $form->load([]);

        $this->assertTrue($form->validate());

        $this->assertTranslationPath($form, 'core', 'am', 'base');
    }

    public function testLoadModule()
    {
        $form = new TranslationForm();
        $form->load([
            'moduleId' => 'translation',
        ]);

        $this->assertTrue($form->validate());

        $this->assertTranslationPath($form, 'translation', 'am', 'base');
    }

    public function testLoadInvalidModule()
    {
        $form = new TranslationForm();
        $form->load(['moduleId' => 'xxx']);
        $this->assertFalse($form->validate());
    }

    public function testLoadLanguage()
    {
        $form = new TranslationForm();
        $form->load([
            'moduleId' => 'translation',
            'language' => 'de',
        ]);

        $this->assertTranslationPath($form, 'translation', 'de', 'base');
    }

    public function testLoadInvalidLanguage()
    {
        $form = new TranslationForm();
        $form->load([
            'moduleId' => 'translation',
            'language' => 'xx',
        ]);
        $this->assertFalse($form->validate());
    }

    public function testLoadFile()
    {
        $form = new TranslationForm();
        $form->load([
            'moduleId' => 'translation',
            'language' => 'de',
            'file' => 'test',
        ]);

        $this->assertTranslationPath($form, 'translation', 'de', 'test');
    }

    public function testLoadInvalidFile()
    {
        $form = new TranslationForm();
        $form->load([
            'moduleId' => 'translation',
            'language' => 'de',
            'file' => 'xxx',
        ]);

        $this->assertFalse($form->validate());
    }

    public function testSaveFileAsAdmin()
    {
        $this->becomeUser('Admin');
        $form = new TranslationForm();
        $form->load([
            'moduleId' => 'translation',
            'language' => 'de',
            'file' => 'test',
            $this->testMessageTID => 'Dies isttt ein test',
        ]);

        $this->assertTrue($form->save());

        $this->assertCount(1, TranslationLog::findAll([
            'language' => 'de',
            'module_id' => 'translation',
            'file' => 'test',
            'message' => $this->testMessage,
        ]));

        $result = $form->basePath->getMessageFile('test')->getMessages('de')[$this->testMessage];
        $this->assertEquals($result, 'Dies isttt ein test');

        $form = new TranslationForm();
        $form->load([
            'moduleId' => 'translation',
            'language' => 'de',
            'file' => 'test',
            $this->testMessageTID => 'Dies ist ein test',
        ]);

        $this->assertTrue($form->save());

        $logs = TranslationLog::find()->where([
            'language' => 'de',
            'module_id' => 'translation',
            'file' => 'test',
            'message' => $this->testMessage,
        ])->orderBy('id DESC')->all();

        $this->assertCount(2, $logs);

        $this->assertEquals($logs[0]->translation_old, 'Dies isttt ein test');
        $this->assertEquals($logs[0]->translation, 'Dies ist ein test');


        $result = $form->basePath->getMessageFile('test')->getMessages('de')[$this->testMessage];
        $this->assertEquals($result, 'Dies ist ein test');
    }

    public function testSaveFileAsMember()
    {
        $this->becomeUser('User2');
        $form = new TranslationForm();
        $this->assertTrue($form->load([
            'moduleId' => 'translation',
            'language' => 'de',
            'file' => 'test',
            $this->testMessageTID => 'Dies isttt ein test',
        ]));

        $this->assertTrue($form->save());

        $result = $form->basePath->getMessageFile('test')->getMessages('de')[$this->testMessage];
        $this->assertEquals($result, 'Dies isttt ein test');
    }

    public function testSaveFileAsNonMember()
    {
        $this->becomeUser('User3');

        $form = new TranslationForm();
        $this->assertTrue($form->load([
            'moduleId' => 'translation',
            'language' => 'de',
            'file' => 'test',
            $this->testMessageTID => 'Dies isttt ein test',
        ]));

        $this->assertFalse($form->save());

        $result = $form->basePath->getMessageFile('test')->getMessages('de')[$this->testMessage];
        $this->assertNotEquals($result, 'Dies isttt ein test');
    }

    /**
     * Empty translations should not overwrite existing translations.
     *
     * @throws \yii\base\Exception
     */
    public function testSaveFileEmptyMessage()
    {
        $this->becomeUser('Admin');
        $form = new TranslationForm();
        $this->assertTrue($form->load([
            'moduleId' => 'translation',
            'language' => 'de',
            'file' => 'test',
            $this->testMessageTID => 'Dies ist ein test',
        ]));

        $this->assertTrue($form->save());

        $form = new TranslationForm();
        $this->assertTrue($form->load([
            'moduleId' => 'translation',
            'language' => 'de',
            'file' => 'test',
            $this->testMessageTID => '',
        ]));

        $this->assertTrue($form->save());

        $result = $form->basePath->getMessageFile('test')->getMessages('de')[$this->testMessage];
        $this->assertEquals($result, 'Dies ist ein test');
    }

    public function testSaveAllLanguages()
    {
        $this->becomeUser('Admin');
        $space = Space::findOne(['id' => 1]);

        foreach (Languages::getAllTranslatableLanguages() as $language) {
            $this->assertNotFalse($space->updateAttributes(['name' => Languages::getSpaceNameByLangauge($language)]));
            $form = new TranslationForm();
            $this->assertTrue($form->load([
                'moduleId' => 'translation',
                'language' => $language,
                'file' => 'test',
                $this->testMessageTID => $language . '_translated',
            ]));

            $this->assertTrue($form->validate());
            $this->assertTranslationPath($form, 'translation', $language, 'test');
        }
    }

    public function testSaveNonTranslatableLanguages()
    {
        $this->becomeUser('Admin');
        $space = Space::findOne(['id' => 1]);

        foreach (['en-US', 'en-GB'] as $language) {
            $this->assertNotFalse($space->updateAttributes(['name' => Languages::getSpaceNameByLangauge($language)]));
            $form = new TranslationForm();
            $form->load([
                'moduleId' => 'translation',
                'language' => $language,
                'file' => 'test',
                $this->testMessageTID => $language . '_translated',
            ]);

            $this->assertFalse($form->validate());
            $this->assertNotEmpty($form->getErrors('language'));
        }
    }

    protected function assertTranslationPath(TranslationForm $form, $moduleId, $language, $baseName)
    {
        $this->assertTrue($form->validate());

        $moduleAlias = $form->basePath->isCoreModulePath() ? 'humhub' : $moduleId;

        $this->assertEquals($moduleId, $form->getMessageModuleId());
        $this->assertEquals($moduleId, $form->basePath->moduleId);
        $this->assertEquals($moduleId, $form->messageFile->moduleId);
        $this->assertEquals($language, $form->getMessageLanguage());
        $this->assertEquals($baseName, $form->getMessageBasename());
        $this->assertEquals($baseName, $form->messageFile->getBaseName());
        $this->assertEquals($baseName . '.php', $form->messageFile->getFileName());
        $this->assertEqualAlias($form->messageFile->getPath($form->language), "@$moduleAlias/messages/$language/$baseName.php");
        $this->assertEqualAlias($form->basePath->getPath(), "@$moduleAlias/messages");
        $this->assertTrue($form->basePath->validate());
        $this->assertTrue($form->messageFile->validate());
    }
}
