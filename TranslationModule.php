<?php

class TranslationModule extends HWebModule
{

    /**
     * Returns a list of available modules
     */
    public function getModuleClasses($language = '')
    {

        $modules = array();

        $modules['Core'] = ' HumHub Core';
        $modules['InstallerModule'] = 'InstallerModule';

        foreach (Yii::app()->modules as $module => $def) {
            $class = explode(".", $def['class']);
            $moduleClass = $class[count($class) - 1];

            try {
                $class = new ReflectionClass($moduleClass);
                $modules[$moduleClass] = $moduleClass;
            } catch (Exception $e) {
                ;
            }
        }

        asort($modules);

        return $modules;
    }

    public function getModulePercentage($moduleClass, $language)
    {
        $countTotal = 0;
        $countTranslated = 0;

        foreach ($this->getFiles($moduleClass, $language) as $file) {
            $fileName = $this->getTranslationFile($moduleClass, $language, $file);
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

        if (Yii::app() instanceof CWebApplication) {
            if (!Yii::app()->user->isAdmin()) {
                $userLanguages = array();

                $spaceLanguages = array_map(function($space) {
                    return strtolower($space->name);
                }, SpaceMembership::GetUserSpaces());

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

        foreach ($this->getModuleClasses() as $moduleClass) {
            foreach ($this->getFiles($moduleClass, $language) as $file) {
                $fileName = $this->getTranslationFile($moduleClass, $language, $file);
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
    public function getFiles($moduleClass, $language)
    {
        $sections = array();

        $directory = $this->getMessageBasePath($moduleClass) . DIRECTORY_SEPARATOR . $language;
        if (is_dir($directory)) {
            $files = scandir($directory);

            foreach ($files as $file) {
                if ($file == '.' || $file == '..' || $file == 'Browser.php' || $file == 'yii.php' || $file == 'zii.php' || $file == 'ui.php')
                    continue;

                $file = basename($file, '.php');
                $sections[$file] = $file;
            }
        }
        return $sections;
    }

    public function getFilePercentage($file, $moduleClass, $language)
    {
        $countTotal = 0;
        $countTranslated = 0;

        $fileName = $this->getTranslationFile($moduleClass, $language, $file);
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
    private function getMessageBasePath($moduleClass = "Core")
    {
        if ($moduleClass == 'Core') {
            return Yii::app()->basePath . DIRECTORY_SEPARATOR . 'messages';
        }

        // Fix to include normally disabled InstallerModule class
        if ($moduleClass == 'InstallerModule') {
            require_once(Yii::getPathOfAlias('application.modules_core.installer') . DIRECTORY_SEPARATOR . 'InstallerModule.php');
        }

        try {
            $class = new ReflectionClass($moduleClass);
            return dirname($class->getFileName()) . DIRECTORY_SEPARATOR . 'messages';
        } catch (Exception $e) {
            return "";
        }
    }

    public function getTranslationFile($moduleClass, $language, $file)
    {
        return $this->getMessageBasePath($moduleClass) . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . $file . ".php";
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
        $array = str_replace("\r", '', var_export($messages, true));
        $content = <<<EOD
<?php
return $array;

EOD;

        file_put_contents($file, $content);
    }

    /**
     * On AdminNavigationWidget init, this callback will be called
     * to add some extra navigation items.
     * 
     * (The event is catched in example/autostart.php)
     * 
     * @param type $event
     */
    public static function onAdminMenuInit($event)
    {
        $event->sender->addItem(array(
            'label' => Yii::t('TranslationModule.base', 'Translation Manager'),
            'url' => Yii::app()->createUrl('//translation/translate'),
            'icon' => '<i class="fa fa-align-left"></i>',
            'group' => 'manage',
            'sortOrder' => 1000,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'translation' && Yii::app()->controller->id == 'translate'),
            'newItemCount' => 0
        ));
    }

    public static function onTopMenuInit($event)
    {
        $event->sender->addItem(array(
            'label' => Yii::t('TranslationModule.base', 'Translations'),
            'url' => Yii::app()->createUrl('//translation/translate', array('uguid' => Yii::app()->user->guid)),
            'icon' => '<i class="fa fa-align-left"></i>',
            'isActive' => (Yii::app()->controller && Yii::app()->controller->module && Yii::app()->controller->module->id == 'translation'),
            'sortOrder' => 700,
        ));
    }

    public static function onConsoleApplicationInit($event)
    {
        Yii::app()->addCommand('translation', 'application.modules.translation.console.TranslationTool');
    }

}
