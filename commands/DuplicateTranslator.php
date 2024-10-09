<?php

namespace humhub\modules\translation\commands;

use humhub\modules\translation\models\BasePath;
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
class DuplicateTranslator extends TranslationCommand
{
    public const RESULT_INDEX_MESSAGE_COUNT = 0;
    public const RESULT_INDEX_TRANSLATED_MESSAGE_COUNT = 1;
    public const RESULT_INDEX_TRANSLATED_DUPLICATES = 2;


    /**
     * @param $language
     * @return array
     * @throws \yii\base\Exception
     */
    public static function translateDuplicatesForLanguage($language, $module = null): array
    {
        static::log("\nHandling Language: " . $language);

        $moduleIds = $module ? [$module] : BasePath::getModuleIds();

        /**
         * Collect all active translations of this language
         */
        $allTranslatedMessages = [];
        $messageCount = 0;
        foreach ($moduleIds as $moduleId) {
            $basePath = BasePath::getBasePath($moduleId);

            foreach ($basePath->getMessageFiles($language) as $messageFile) {
                foreach ($messageFile->getMessages($language) as $original => $translated) {
                    $messageCount++;
                    $translated = str_replace("@@", "", $translated);
                    if (!empty($translated)) {
                        $allTranslatedMessages[$original] = $translated;
                    }
                }
            }
        }

        /**
         * Load and append non active translations from archive
         */
        $archiveFile = Yii::getAlias('@humhub/messages/' . $language . '/archive.json');
        if (is_file($archiveFile)) {
            $archiveMessages = Json::decode(file_get_contents($archiveFile));
            foreach ($archiveMessages as $key => $msg) {
                if (!isset($allTranslatedMessages[$key]) && !empty($msg[0])) {
                    $allTranslatedMessages[$key] = $msg[0];
                }
            }
        }

        /**
         * Update untranslated message duplicates
         */
        $autoTranslated = 0;
        foreach (BasePath::getModuleIds() as $moduleId) {
            $basePath = BasePath::getBasePath($moduleId);
            foreach ($basePath->getMessageFiles($language) as $messageFile) {

                $messagesChanged = false;
                $messages = $messageFile->getMessages($language);

                foreach ($messageFile->getMessages($language) as $original => $translated) {
                    if (empty($translated) && isset($allTranslatedMessages[$original]) && !empty($allTranslatedMessages[$original])) {
                        $messages[$original] = $allTranslatedMessages[$original];
                        $autoTranslated++;
                        $messagesChanged = true;
                    }
                }

                if ($messagesChanged) {
                    $messageFile->updateTranslations($language, $messages);
                }
            }
        }

        $result =  [$messageCount, count($allTranslatedMessages), $autoTranslated];

        static::log("\tTotal messages:" . $result[0]);
        static::log("\tTranslated:" . $result[1]);
        static::log("\tAuto translated:" . $result[2]);
        static::log("");

        return $result;
    }
}
