<?php


namespace humhub\modules\translation\commands;

use humhub\modules\translation\models\BasePath;
use humhub\modules\translation\models\Languages;
use humhub\modules\translation\Module;
use Yii;
use yii\helpers\Json;

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
    const RESULT_INDEX_MESSAGE_COUNT = 0;
    const RESULT_INDEX_TRANSLATED_MESSAGE_COUNT = 1;
    const RESULT_INDEX_TRANSLATED_DUPLICATES = 2;

    /**
     * @param $language
     * @throws \yii\base\Exception
     */
    public static function rename($moduleId, $oldCategory, $newCategory, $language = null)
    {
        $basePath = BasePath::getBasePath($moduleId);

        if($basePath->validate()) {
            static::log('Could not rename category due to invalid basepath: '.$basePath->getPath());
        }

        $languages = $language ? [$language] : Languages::getAllTranslatableLanguages();

        foreach ($languages as $lang) {
            $oldFile = $basePath->getMessageFile($oldCategory);
            $newFile = $basePath->getMessageFile($newCategory);

            if (!$oldFile->validate() || !$oldFile->validateLanguagePath($lang)) {
                static::log("!Skipped: " . $oldFile->getPath($lang) . "\n");
                continue;
            }

            static::log('Rename message file '.$oldFile->getPath($lang). ' to '.$newFile->getPath($lang));

            $oldMessages = $oldFile->getMessages($lang);
            $created = false;

            if (!$newFile->validateLanguagePath($lang)) {
                $newMessages = [];
                $created = true;
            } else {
                $newMessages = $newFile->getMessages($lang);
            }

            $newFile->updateTranslations($lang, array_merge($oldMessages, $newMessages), true);

            unlink($oldFile->getPath($lang));

            static::log(($created ? "Created: " : "Updated: ") . $newFile->getPath($lang));
            static::log("Deleted: " . $oldFile->getPath($lang));
        }
    }
}