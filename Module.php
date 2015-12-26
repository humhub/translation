<?php

namespace humhub\modules\translation;

use Yii;
use yii\helpers\Url;

class Module extends \humhub\components\Module
{

    /**
     * Returns a list of available modules
     */
    public function getModuleIds($language = '')
    {

        $modules = array();

        $modules['core'] = 'core';
        #$modules['InstallerModule'] = 'InstallerModule';

        foreach (Yii::$app->moduleManager->getModules(['includeCoreModules' => true]) as $moduleId => $def) {
            $modules[$moduleId] = $moduleId;
        }

        asort($modules);
        return $modules;
    }

    public function getModulePercentage($moduleId, $language)
    {
        $countTotal = 0;
        $countTranslated = 0;

        foreach ($this->getFiles($moduleId, $language) as $file) {
            $fileName = $this->getTranslationFile($moduleId, $language, $file);
            if ($fileName != "") {
                $messages = $this->getTranslationMessages($fileName);
                $countTranslated += count(array_filter($messages));
                $countTotal += count($messages);
            }
        }
        if ($countTotal != 0) {
            return floor($countTranslated * 100 / $countTotal);
        }
        return 0;
    }

    /**
     * Returns a list of languages
     */
    public function getLanguages()
    {
        $languages = array();

        if (!is_dir($this->getMessageBasePath()))
            return $languages;

        $files = scandir($this->getMessageBasePath());

        foreach ($files as $file) {
            if ($file == '.' || $file == '..')
                continue;
            $languages[$file] = $file;
        }

        if (!Yii::$app->request->isConsoleRequest) {
            if (!Yii::$app->user->isAdmin()) {
                $userLanguages = array();

                $spaceLanguages = array_map(function($space) {
                    return strtolower($space->name);
                }, \humhub\modules\space\models\Membership::GetUserSpaces());

                foreach ($spaceLanguages as $sp) {
                    if (in_array($sp, $languages)) {
                        $userLanguages[$sp] = $sp;
                    }
                }

                return $userLanguages;
            }
        }
        return $languages;
    }

    public function getLanguagePercentage($language)
    {
        $countTotal = 0;
        $countTranslated = 0;

        foreach ($this->getModuleIds() as $moduleId) {
            foreach ($this->getFiles($moduleId, $language) as $file) {
                $fileName = $this->getTranslationFile($moduleId, $language, $file);
                if ($fileName != "") {
                    $messages = $this->getTranslationMessages($fileName);
                    $countTranslated += count(array_filter($messages));
                    $countTotal += count($messages);
                }
            }
        }

        if ($countTotal != 0) {
            return floor($countTranslated * 100 / $countTotal);
        }
        return 0;
    }

    /**
     * Returns a list of available files for a module
     * 
     * @param type $module
     */
    public function getFiles($moduleId, $language)
    {
        $sections = array();

        $directory = $this->getMessageBasePath($moduleId) . DIRECTORY_SEPARATOR . $language;
        if (is_dir($directory)) {
            $files = scandir($directory);

            foreach ($files as $file) {
                if ($file == 'Browser.php' || $file == 'yii.php' || $file == 'zii.php' || $file == 'ui.php')
                    continue;

                if (!preg_match('/\.php$/', $file)) {
                    continue;
                }
                
                $file = basename($file, '.php');
                $sections[$file] = $file;
            }
        }
        return $sections;
    }

    public function getFilePercentage($file, $moduleId, $language)
    {
        $countTotal = 0;
        $countTranslated = 0;

        $fileName = $this->getTranslationFile($moduleId, $language, $file);
        if ($fileName != "") {
            $messages = $this->getTranslationMessages($fileName);
            $countTranslated += count(array_filter($messages));
            $countTotal += count($messages);
        }

        if ($countTotal != 0) {
            return floor($countTranslated * 100 / $countTotal);
        }
        return 0;
    }

    /**
     * Returns base path for current module
     */
    private function getMessageBasePath($moduleId = "core")
    {
        if ($moduleId == 'core') {
            return Yii::getAlias('@humhub/messages');
        }

        $module = Yii::$app->moduleManager->getModule($moduleId);
        return $module->getBasePath() . '/messages';
    }

    public function getTranslationFile($moduleId, $language, $file)
    {
        return $this->getMessageBasePath($moduleId) . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . $file . ".php";
    }

    /**
     * Returns all Messages
     * 
     * @param type $lang
     * @param string $section
     * @return type
     */
    public function getTranslationMessages($file)
    {
        return require($file);
    }

    public function saveTranslationMessages($file, $messages)
    {
        ksort($messages);
        $array = str_replace("\r", '', var_export($messages, true));
        $content = <<<EOD
<?php
return $array;

EOD;

        file_put_contents($file, $content);
    }

    public static function onTopMenuInit($event)
    {
        $event->sender->addItem(array(
            'label' => Yii::t('TranslationModule.base', 'Translations'),
            'url' => Url::to(['/translation/translate']),
            'icon' => '<i class="fa fa-align-left"></i>',
            'isActive' => (Yii::$app->controller && Yii::$app->controller->module && Yii::$app->controller->module->id == 'translation'),
            'sortOrder' => 700,
        ));
    }

    public static function onConsoleApplicationInit($event)
    {
        $application = $event->sender;
        $application->controllerMap['translation'] = commands\TranslationController::className();
    }

}
