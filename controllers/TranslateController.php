<?php

class TranslateController extends Controller {

   # public $subLayout = "application.modules_core.admin.views._layout";
    public $language;
    public $moduleId;
    public $category;

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'users' => array('@'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    public function actionIndex() {

        $modules = $this->getModules();
        $moduleKey = (int) Yii::app()->request->getParam('moduleId', 0);
        if (isset($modules[$moduleKey])) {
            $this->moduleId = $modules[$moduleKey];
        } else {
            $this->moduleId = $modules[0];
            $moduleKey = 0;
        }

        $languages = $this->getLanguages();
        $languageKey = Yii::app()->request->getParam('language', 0);
        if (isset($languages[$languageKey])) {
            $this->language = $languages[$languageKey];
        } else {
            $this->language = $languages[0];
            $languageKey = 0;
        }

        $categories = $this->getCategories();
        $categoryKey = Yii::app()->request->getParam('category', 0);
        if (isset($categories[$categoryKey])) {
            $this->category = $categories[$categoryKey];
        } else {
            $this->category = $categories[0];
            $categoryKey = 0;
        }

        $messages = $this->getMessages();

        // Render Template
        $this->render('index', array(
            'language' => $this->language,
            'category' => $this->category,
            'moduleId' => $this->moduleId,
            'languageKey' => $languageKey,
            'categoryKey' => $categoryKey,
            'moduleKey' => $moduleKey,
            'modules' => $modules,
            'languages' => $languages,
            'categories' => $categories,
            'messages' => $messages,
        ));
    }

    public function actionSave() {

        $this->forcePostRequest();

        $modules = $this->getModules();
        $moduleKey = (int) Yii::app()->request->getParam('moduleId', 0);
        if (isset($modules[$moduleKey])) {
            $this->moduleId = $modules[$moduleKey];
        }

        $languages = $this->getLanguages();
        $languageKey = Yii::app()->request->getParam('language', 0);
        if (isset($languages[$languageKey])) {
            $this->language = $languages[$languageKey];
        }

        $categories = $this->getCategories();
        $categoryKey = Yii::app()->request->getParam('category', 0);
        if (isset($categories[$categoryKey])) {
            $this->category = $categories[$categoryKey];
        }

        $messages = $this->getMessages();
        foreach ($messages as $orginal => $translated) {
            $newTranslation = Yii::app()->request->getParam('tid_' . md5($orginal));
            if ($newTranslation != "") {
                $messages[$orginal] = $newTranslation;
            }
        }

        $this->getSaveMessages($messages);

        $this->redirect($this->createUrl('index', array('moduleId' => $moduleKey, 'language' => $languageKey, 'category' => $categoryKey)));
    }

    /**
     * Returns all Messages
     * 
     * @param type $lang
     * @param string $section
     * @return type
     */
    private function getMessages() {
        $file = $this->getBasePath() . DIRECTORY_SEPARATOR . $this->language . DIRECTORY_SEPARATOR . $this->category . ".php";
        return require($file);
    }

    private function getSaveMessages($messages) {
        $file = $this->getBasePath() . DIRECTORY_SEPARATOR . $this->language . DIRECTORY_SEPARATOR . $this->category . ".php";

        $array = str_replace("\r", '', var_export($messages, true));
        $content = <<<EOD
<?php
return $array;

EOD;

        file_put_contents($file, $content);
    }

    public function getSectionPercent() {
        return 0;
        /*
          $messages = $this->getMessages($lang, $section);

          $filled = 0;
          foreach ($messages as $message) {
          if ($message != "")
          $filled++;
          }

          if ($filled == 0)
          return 0;

          return round(($filled * 100) / count($messages));
         * 
         */
    }

    private function getModules() {

        $modules = array();
        #$modules[] = 'Core';

        foreach (Yii::app()->modules as $module => $def) {
            $class = explode(".", $def['class']);
            $moduleClass = $class[count($class) - 1];

            try {
                $class = new ReflectionClass($moduleClass);
                $modules[] = $moduleClass;
            } catch (Exception $e) {
                ;
            }
        }

        sort($modules);
        array_unshift($modules, 'Core');

        // Remove Modules without messages directory
        foreach ($modules as $i => $module) {
            $this->moduleId = $module;
            if (!is_dir($this->getBasePath())) {
                unset($modules[$i]);
            }
        }

        return $modules;
    }

    private function getLanguages() {

        $languages = array();

        if (!is_dir($this->getBasePath()))
            return $languages;

        $files = scandir($this->getBasePath());

        foreach ($files as $file) {
            if ($file == '.' || $file == '..')
                continue;
            $languages[] = $file;
        }

        return $languages;
    }

    private function getCategories() {

        $sections = array();
        $files = scandir($this->getBasePath() . DIRECTORY_SEPARATOR . $this->language);

        foreach ($files as $file) {
            if ($file == '.' || $file == '..' || $file == 'Browser.php' || $file == 'yii.php' || $file == 'zii.php' || $file == 'ui.php')
                continue;

            $file = basename($file, '.php');

            $sections[] = $file;
        }
        return $sections;
    }

    /**
     * Returns base path for messages
     */
    private function getBasePath() {
        if ($this->moduleId == 'Core') {
            return Yii::app()->basePath . DIRECTORY_SEPARATOR . 'messages';
        }

        try {
            $class = new ReflectionClass($this->moduleId);
            return dirname($class->getFileName()) . DIRECTORY_SEPARATOR . 'messages';
        } catch (Exception $e) {
            return "";
        }
    }

}
