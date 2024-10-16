<?php

namespace humhub\modules\translation\commands;

use humhub\modules\translation\models\BasePath;

/**
 * Automatically translates message duplicates.
 *
 * e.g.:
 *
 * Yii::t('PostModule.base', 'Save');
 * Yii::t('MailModule.base', 'Save');
 *
 * If only one of those was translated, we reuse existing translations.
 *
 * Besides searching for active translations, this function searches in the language archive.json, which also
 * contains old translations.
 */
class RenameTranslationCategory extends TranslationCommand
{
    /**
     * Rename single or several categories
     *
     * @param string $moduleId
     * @param string $oldCategory
     * @param string $newCategory
     * @param string|null $language null - apply for all languages
     * @return void
     * @throws \yii\base\Exception
     */
    public static function rename($moduleId, $oldCategory, $newCategory, $language = null): void
    {
        $basePath = BasePath::getBasePath($moduleId);

        if (!$basePath->validate()) {
            static::log('Could not rename category due to invalid basepath: ' . $basePath->getPath());
            return;
        }

        $oldCategories = $oldCategory === '*'
            ? $basePath->getAllCategories($newCategory)
            : explode(',', $oldCategory);

        if (empty($oldCategories)) {
            static::log('Old category is not found.');
        }

        foreach ($oldCategories as $oldCategory) {
            self::renameLanguageFiles($basePath, $oldCategory, $newCategory, $language);
            self::updateCategoryInPhpFiles($basePath, $oldCategory, $newCategory);
        }
    }

    /**
     * Rename language files
     *
     * @param BasePath $basePath
     * @param string $oldCategory
     * @param string $newCategory
     * @param string|null $language null - apply for all languages
     * @throws \yii\base\Exception
     */
    private static function renameLanguageFiles(BasePath $basePath, $oldCategory, $newCategory, $language = null): void
    {
        $headerText = 'Rename message files from category "' . $oldCategory . '" to "' . $newCategory . '"';
        $headerLine = str_repeat('=', strlen($headerText));
        static::log(PHP_EOL . $headerLine);
        static::log($headerText);
        static::log($headerLine . PHP_EOL);

        $languages = $language ? [$language] : $basePath->getAllLanguages();

        foreach ($languages as $lang) {
            $oldFile = $basePath->getMessageFile($oldCategory);
            $newFile = $basePath->getMessageFile($newCategory);

            if (!$oldFile->validate() || !$oldFile->validateLanguagePath($lang)) {
                static::log("!Skipped: " . $oldFile->getPath($lang) . " ($lang)\n");
                continue;
            }

            static::log('Rename message file ' . $oldFile->getPath($lang) . ' to ' . $newFile->getPath($lang));

            $oldMessages = $oldFile->getMessages($lang);
            $created = false;

            if (!$newFile->validateLanguagePath($lang)) {
                $newMessages = [];
                $created = true;
            } else {
                $newMessages = $newFile->getMessages($lang);
            }

            $newFile->updateTranslations($lang, array_merge($oldMessages, $newMessages), true);

            $oldFilePath = $oldFile->getPath($lang);
            unlink($oldFilePath);

            static::log(($created ? 'Created: ' : 'Updated: ') . $newFile->getPath($lang));
            static::log('Deleted: ' . $oldFilePath);
        }
    }

    private static function updateCategoryInPhpFiles(BasePath $basePath, $oldCategory, $newCategory): void
    {
        $oldModuleCategory = $basePath->getModuleCategory($oldCategory);
        $newModuleCategory = $basePath->getModuleCategory($newCategory);

        foreach ($basePath->getModulePhpFiles() as $filePath) {
            $oldFileContent = file_get_contents($filePath);
            if (!str_contains($oldFileContent, $oldModuleCategory)) {
                continue;
            }

            $newFileContent = preg_replace(
                '/(Yii::t[\s\r\n]*\([\s\r\n]*([\'"]))' . preg_quote($oldModuleCategory, '/') . '(\2)/s',
                '$1' . $newModuleCategory . '$3',
                $oldFileContent,
            );

            if ($newFileContent === $oldFileContent) {
                continue;
            }

            if (file_put_contents($filePath, $newFileContent)) {
                static::log('PHP file ' . $filePath . ' is updated with new category "' . $newCategory . '"');
            } else {
                static::log('CANNOT update PHP file ' . $filePath . ' with new category "' . $newCategory . '"');
            }
        }
    }
}
