<?php

namespace humhub\modules\translation\tests\codeception\unit;

use humhub\modules\translation\models\BasePath;
use humhub\modules\translation\commands\DuplicateTranslator;
use humhub\modules\translation\commands\RenameTranslationCategory;
use translation\TranslationTest;
use Yii;

class RenameTranslatorTest extends TranslationTest
{
    public const OLD_CATEGORY = 'old';
    public const NEW_CATEGORY = 'new';
    public const EXISTING_CATEGORY = 'test';

    public function _before()
    {
        parent::_before();
        $basePath = BasePath::getBasePath('translation');

        // Create new test category
        $newCategory = $basePath->getMessageFile(static::OLD_CATEGORY);
        $newCategory->updateTranslations('de', [
            'New message' => 'Neue Nachricht',
        ], true);
    }

    public function _after()
    {
        $basePath = BasePath::getBasePath('translation');
        $newCategory = $basePath->getMessageFile(static::OLD_CATEGORY);
        if ($newCategory->validateLanguagePath('de')) {
            unlink($newCategory->getPath('de'));
        }

        $newCategory2 = $basePath->getMessageFile(static::NEW_CATEGORY);
        if ($newCategory2->validateLanguagePath('de')) {
            unlink($newCategory2->getPath('de'));
        }
    }

    public function testRenameCreate()
    {
        $basePath = BasePath::getBasePath('translation');

        // Make sure new category 2 does not exist
        $this->assertFalse($basePath->getMessageFile(static::NEW_CATEGORY)->validateLanguagePath('de'));

        $oldCategory = $basePath->getMessageFile(static::OLD_CATEGORY);

        // Make sure old category exists
        $this->assertTrue($oldCategory->validateLanguagePath('de'));

        // Test new message category
        $this->assertEquals('Neue Nachricht', $basePath->getMessageFile(static::OLD_CATEGORY)->getTranslation('de', 'New message'));

        // Move to a new category
        RenameTranslationCategory::rename('translation', static::OLD_CATEGORY, static::NEW_CATEGORY);

        // Make sure old category was deleted
        $this->assertFalse($oldCategory->validateLanguagePath('de'));

        $newCategory2 = $basePath->getMessageFile(static::NEW_CATEGORY);
        $this->assertTrue($newCategory2->validateLanguagePath('de'));

        $this->assertEquals('Neue Nachricht', $basePath->getMessageFile(static::NEW_CATEGORY)->getTranslation('de', 'New message'));
    }

    public function testRenameMerge()
    {
        $basePath = BasePath::getBasePath('translation');

        // Make sure new message is currently not available in test category
        $this->assertEmpty($basePath->getMessageFile(static::EXISTING_CATEGORY)->getTranslation('de', 'New message'));

        $oldCategory = $basePath->getMessageFile(static::OLD_CATEGORY);

        // Make sure old category exists
        $this->assertTrue($oldCategory->validateLanguagePath('de'));

        // Test new message category
        $this->assertEquals('Neue Nachricht', $basePath->getMessageFile(static::OLD_CATEGORY)->getTranslation('de', 'New message'));

        // Merge new message category with old
        RenameTranslationCategory::rename('translation', static::OLD_CATEGORY, static::EXISTING_CATEGORY);

        // Make sure old category was deleted
        $this->assertFalse($oldCategory->validateLanguagePath('de'));

        // Fetch merged message
        $this->assertEquals('Neue Nachricht', $basePath->getMessageFile(static::EXISTING_CATEGORY)->getTranslation('de', 'New message'));
    }
}
