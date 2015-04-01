<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * Console tools for manage spaces
 *
 * @package humhub.modules_core.space.console
 * @since 0.5
 */
class TranslationTool extends HConsoleCommand
{

    public function init()
    {
        $this->printHeader('Translation  Tools');
        return parent::init();
    }

    public function beforeAction($action, $params)
    {

        return parent::beforeAction($action, $params);
    }

    public function actionRenameOriginal($args)
    {
        if (!isset($args[0])) {
            print "Error: Orginal text parameter required!\n\n";
            print $this->getHelp();
            return;
        }

        if (!isset($args[1])) {
            print "Error: Next text parameter required!\n\n";
            print $this->getHelp();
            return;
        }

        $keyBefore = $args[0];
        $keyAfter = $args[1];

        $translationModule = Yii::app()->getModule('translation');
        foreach ($translationModule->getLanguages() as $language) {
            print "\nHandling Language: " . $language . "\n";
            $renamed = 0;

            foreach ($translationModule->getModuleClasses() as $moduleClass => $moduleTitle) {
                foreach ($translationModule->getFiles($moduleClass, $language) as $file => $title) {
                    $fileName = $translationModule->getTranslationFile($moduleClass, $language, $file);
                    $messages = $translationModule->getTranslationMessages($fileName);

                    if (isset($messages[$keyBefore])) {
                        $messages[$keyAfter] = $messages[$keyBefore];
                        unset($messages[$keyBefore]);
                        $translationModule->saveTranslationMessages($fileName, $messages);
                        $renamed++;
                    }
                }
            }
            print "\tReplaced " . $renamed . " entries.\n";
        }
    }

    public function actionHandleDuplicate($args)
    {

        $translationModule = Yii::app()->getModule('translation');

        foreach ($translationModule->getLanguages() as $language => $title) {
            print "\nHandling Language: " . $language . "\n";

            /**
             * Collect all Translated Messages
             */
            $allTranslatedMessages = array();
            $messageCount = 0;
            foreach ($translationModule->getModuleClasses() as $moduleClass => $title) {
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
            foreach ($translationModule->getModuleClasses() as $moduleClass => $title) {
                foreach ($translationModule->getFiles($moduleClass, $language) as $file => $title) {
                    $fileName = $translationModule->getTranslationFile($moduleClass, $language, $file);
                    $messages = $translationModule->getTranslationMessages($fileName);
                    $messagesChanged = false;

                    foreach ($messages as $original => $translated) {
                        if ($translated == "" && $allTranslatedMessages[$original] != "") {
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

    public function getHelp()
    {
        return <<<EOD
USAGE
  yiic translation [action] [parameter]

DESCRIPTION
  This command provides some translation tool extras. 

EXAMPLES
 * yiic translation handleDuplicate
   Automatically translate found duplicates
        
 * yiic translation renameOrginal "original key" "new key"
   Renames an original translation without touching translated values

EOD;
    }

}
