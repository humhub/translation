<?php

namespace humhub\modules\translation\controllers;

use Yii;
use yii\web\HttpException;
use yii\helpers\Url;

class TranslateController extends \humhub\components\Controller
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
    public $moduleId;

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
     * Current loaded message file for moduleId/file/language combination
     * 
     * @var string
     */
    public $messages;

    public function beforeAction($action)
    {

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        /**
         * Load language
         */
        $languages = $this->module->getLanguages();
        if (count($languages) == 0) {
            throw new HttpException(404, 'No language available!');
        }
        $this->language = Yii::$app->request->get('language', array_values($languages)[0]);
        if (!in_array($this->language, $languages)) {
            throw new HttpException(404, 'Language not found!');
        }

        /**
         * Load given Module Class
         */
        $this->moduleId = Yii::$app->request->get('moduleId', 'core');
        if (!in_array($this->moduleId, array_keys($this->module->getModuleIds()))) {
            throw new HttpException(404, 'Module not found!');
        }
        /**
         * Load given File 
         */
        $files = $this->module->getFiles($this->moduleId, $this->language);
        if (count($files) == 0) {
            throw new HttpException(400, 'Files not found!');
        }
        $this->file = Yii::$app->request->get('file', '');
        if (!in_array($this->file, $this->module->getFiles($this->moduleId, $this->language))) {
            $this->file = array_values($files)[0];
        }

        /**
         * Get Messages
         */
        $this->messages = array();
        $this->messageFileName = $this->module->getTranslationFile($this->moduleId, $this->language, $this->file);
        if (file_exists($this->messageFileName)) {
            $this->messages = $this->module->getTranslationMessages($this->messageFileName);
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

        $moduleIds = $this->module->getModuleIds();
        array_walk($moduleIds, function(&$value, $key) {
            $value .= " (" . $this->module->getModulePercentage($key, $this->language) . "%)";
        });


        $files = $this->module->getFiles($this->moduleId, $this->language);
        array_walk($files, function(&$value, $key) {
            $value .= " (" . $this->module->getFilePercentage($key, $this->moduleId, $this->language) . "%)";
        });


        $languages = $this->module->getLanguages();
        array_walk($languages, function(&$value, $key) {
            if ($key == $this->language) {
                $value .= " (" . $this->module->getLanguagePercentage($key) . "%)";
            }
        });

// Render Template
        return $this->render('index', array(
// Available Options
                    'moduleIds' => $moduleIds,
                    'languages' => $languages,
                    'files' => $files,
                    // Current selection
                    'language' => $this->language,
                    'moduleId' => $this->moduleId,
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
                $newTranslation = Yii::$app->request->post('tid_' . md5($orginalMessage));
                $messages[$orginalMessage] = $newTranslation;
            }
            $this->module->saveTranslationMessages($this->messageFileName, $messages);
        }

        $this->redirect(Url::to(['index', 'moduleId' => $this->moduleId, 'language' => $this->language, 'file' => $this->file]));
    }

}
