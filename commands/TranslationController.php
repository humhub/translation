<?php

namespace humhub\modules\translation\commands;

use Yii;

/**
 * Translation MOdule
 *
 * @since 0.5
 */
class TranslationController extends \yii\console\Controller
{

    /**
     * Automatically translates message duplicates
     */
    public function actionHandleDuplicate()
    {

        $translationModule = Yii::$app->getModule('translation');

        foreach ($translationModule->getLanguages() as $language => $title) {
            print "\nHandling Language: " . $language . "\n";

            /**
             * Collect all Translated Messages
             */
            $allTranslatedMessages = array();
            $messageCount = 0;
            foreach ($translationModule->getModuleIds() as $moduleClass => $title) {
                foreach ($translationModule->getFiles($moduleClass, $language) as $file) {
                    $fileName = $translationModule->getTranslationFile($moduleClass, $language, $file);
                    $messages = $translationModule->getTranslationMessages($fileName);
                    $messageCount += count($messages);

                    foreach ($messages as $original => $translated) {
                        $translated = str_replace("@@", "", $translated);
                        if ($translated != "") {
                            $allTranslatedMessages[$original] = $translated;
                        }
                    }
                }
            }

            $autoTranslated = 0;
            foreach ($translationModule->getModuleIds() as $moduleClass => $title) {
                foreach ($translationModule->getFiles($moduleClass, $language) as $file => $title) {
                    $fileName = $translationModule->getTranslationFile($moduleClass, $language, $file);
                    $messages = $translationModule->getTranslationMessages($fileName);
                    $messagesChanged = false;

                    foreach ($messages as $original => $translated) {
                        if ($translated == "" && isset($allTranslatedMessages[$original]) && $allTranslatedMessages[$original] != "") {
                            $messages[$original] = $allTranslatedMessages[$original];
                            $autoTranslated++;
                            $messagesChanged = true;
                        }
                    }

                    if ($messagesChanged) {
                        $translationModule->saveTranslationMessages($fileName, $messages);
                    }
                }
            }

            print "\tTotal messages:" . $messageCount . "\n";
            print "\tTranslated:" . count($allTranslatedMessages) . "\n";
            print "\tAuto translated:" . $autoTranslated . "\n";
        }


        print "\n";
    }

}
