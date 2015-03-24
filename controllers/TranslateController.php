<?php

class TranslateController extends Controller
{

    /**
     * Current active language code e.g. en
     * 
     * @var string
     */
    public $language;

    /**
     * Current active module e.g. Core
     * 
     * @var string 
     */
    public $moduleClass;

    /**
     * Language file inside language / module 
     * 
     * @var string
     */
    public $file;

    /**
     * FileName of message file
     * 
     * @var string
     */
    public $messageFileName = "";

    /**
     * Current loaded message file for moduleClass/file/language combination
     * 
     * @var string
     */
    public $messages;

    /**
     * @return array action filters
     */
    public function filters()
    {
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

    public function beforeAction($action)
    {

        /**
         * Load language
         */
        $languages = $this->getModule()->getLanguages();
        if (count($languages) == 0) {
            $this->render('noLanguage');
            return false;
        }
        $this->language = Yii::app()->request->getParam('language', array_values($languages)[0]);
        if (!in_array($this->language, $languages)) {
            throw new CHttpException(404, 'Language not found!');
        }

        /**
         * Load given Module Class
         */
        $this->moduleClass = Yii::app()->request->getParam('moduleClass', 'Core');
        if (!in_array($this->moduleClass, array_keys($this->getModule()->getModuleClasses()))) {
            throw new CHttpException(404, 'Module not found!');
        }

        /**
         * Load given File 
         */
        $files = $this->getModule()->getFiles($this->moduleClass, $this->language);
        if (count($files) == 0) {
            throw new CHttpException(400, 'Files not found!');
        }
        $this->file = Yii::app()->request->getParam('file', '');
        if (!in_array($this->file, $this->getModule()->getFiles($this->moduleClass, $this->language))) {
            $this->file = array_values($files)[0];
        }

        /**
         * Get Messages
         */
        $this->messages = array();
        $this->messageFileName = $this->getModule()->getTranslationFile($this->moduleClass, $this->language, $this->file);
        if (file_exists($this->messageFileName)) {
            $this->messages = $this->getModule()->getTranslationMessages($this->messageFileName);
        }
        return parent::beforeAction($action);
    }

    /**
     * Inits the Translate Controller
     * 
     * @param type $action
     * @return type
     */
    public function actionIndex()
    {

        $moduleClasses = $this->getModule()->getModuleClasses();
        array_walk($moduleClasses, function(&$value, $key) {
            $value .= " (" . $this->getModule()->getModulePercentage($key, $this->language) . "%)";
        });


        $files = $this->getModule()->getFiles($this->moduleClass, $this->language);
        array_walk($files, function(&$value, $key) {
            $value .= " (" . $this->getModule()->getFilePercentage($key, $this->moduleClass, $this->language) . "%)";
        });


        $languages = $this->getModule()->getLanguages();
        array_walk($languages, function(&$value, $key) {
            if ($key == $this->language) {
                $value .= " (" . $this->getModule()->getLanguagePercentage($key) . "%)";
            }
        });

        // Render Template
        $this->render('index', array(
            // Available Options
            'moduleClasses' => $moduleClasses,
            'languages' => $languages,
            'files' => $files,
            // Current selection
            'language' => $this->language,
            'moduleClass' => $this->moduleClass,
            'file' => $this->file,
            // Translation
            'messages' => $this->messages,
        ));
    }

    public function actionSave()
    {

        $this->forcePostRequest();

        if (count($this->messages) != 0) {
            foreach ($this->messages as $orginalMessage => $oldTranslation) {
                $newTranslation = Yii::app()->request->getParam('tid_' . md5($orginalMessage));
                $messages[$orginalMessage] = $newTranslation;
            }
            $this->getModule()->saveTranslationMessages($this->messageFileName, $messages);
        }

        $this->redirect($this->createUrl('index', array('moduleClass' => $this->moduleClass, 'language' => $this->language, 'file' => $this->file)));
    }

}
