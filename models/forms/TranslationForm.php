<?php

namespace humhub\modules\translation\models\forms;

use humhub\modules\space\models\Space;
use humhub\modules\translation\permissions\ManageTranslations;
use humhub\modules\translation\models\TranslationCoverage;
use humhub\modules\translation\models\TranslationFileIF;
use Yii;
use yii\base\Model;
use humhub\modules\translation\models\BasePath;
use humhub\modules\translation\models\Languages;
use humhub\modules\translation\models\MessageFile;
use humhub\modules\translation\models\TranslationLog;

class TranslationForm extends Model implements TranslationFileIF
{
    /**
     * @var string
     */
    public $language;

    /**
     * @var string
     */
    public $moduleId = BasePath::CORE_MODULE_ID;

    /**
     * @var MessageFile[]
     */
    public $files;

    /**
     * @var BasePath
     */
    public $basePath;

    /**
     * @var string
     */
    public $file;

    /**
     * @var MessageFile
     */
    public $messageFile;

    /**
     * @var array
     */
    public $messages;

    /**
     * @var array
     */
    public $errors = [];

    /**
     * @var array
     */
    public $warnings = [];

    /**
     * @var Space
     */
    public $space;

    public function rules()
    {
        return [
            [['language'], 'validateLanguage'],
            [['language', 'moduleId', 'file'], 'string'],
            [['language', 'moduleId', 'files', 'file', 'messageFile'], 'required'],
            [['file'], 'validateFile'],
        ];
    }

    public function validateFile()
    {
        if (!$this->messageFile || !$this->messageFile->validate()) {
            $this->addError('file', 'Message file not found!');
        }
    }

    public function validateLanguage()
    {
        if (!in_array($this->language, Languages::getTranslatableUserLanguages())) {
            $this->addError('language', 'You are not allowed to translate this language!');
        }
        if (!$this->messageFile || !$this->messageFile->validateLanguagePath($this->language)) {
            $this->addError('language', 'Language file not found!');
        }
    }

    public function attributeLabels()
    {
        return [
            'moduleId' => Yii::t('TranslationModule.base', 'Module'),
            'language' => Yii::t('TranslationModule.base', 'Language'),
            'file' => Yii::t('TranslationModule.base', 'File')
        ];
    }

    /**
     * @var TranslationLog[]
     */
    private $translationLogs = [];

    public function load($data, $formName = null)
    {
        $result = parent::load($data, $formName);

        $dirty = !$this->language;

        $this->language = $this->language ?: Languages::getDefaultLanguage();
        $this->basePath = BasePath::getBasePath($this->moduleId);
        $this->files = $this->basePath->getMessageFiles($this->language);

        if (!$this->file && !empty($this->files)) {
            $dirty = true;
            $this->file = $this->files[0]->getBaseName();
        }

        if ($this->file) {
            $this->messageFile = $this->basePath->getMessageFile($this->file);
        }

        $this->loadMessages();

        $this->autoTranslateEmptyValues();

        // In case the form used any default value instead of loaded value we skip translation loading
        if ($dirty || !$result) {
            return false;
        }

        $this->space = Languages::findSpaceByLanguage($this->language);
        if (!$this->space) {
            $this->addError('space', Yii::t('TranslationModule.base', 'There is no language related space available for language {lang}', ['lang' =>$this->language]));
            return false;
        }

        $this->translationLogs = [];

        foreach ($this->messages as $originalMessage => $oldTranslation) {
            $translationModel = new TranslationLog($this->space, [
                'language' => $this->language,
                'module_id' => $this->moduleId,
                'file' => $this->file,
                'translation_old' => $oldTranslation,
                'message' => $originalMessage,
            ]);

            if ($translationModel->load($data) && !empty($translationModel->translation)) {
                $this->translationLogs[] = $translationModel;
            }
        }

        return $result;
    }

    protected function loadMessages()
    {
        $this->messages = [];
        if ($this->messageFile instanceof MessageFile && $this->messageFile->validate()) {
            $this->messages = $this->messageFile->getMessages($this->language);
        }
    }


    /**
     * Translate automatically with Google translate API
     * $googleApiKey must be set
     */
    protected function autoTranslateEmptyValues ()
    {
        $module = Yii::$app->controller->module; // current module
        if (empty($module->googleApiKey)) {
            return false;
        }

        // Get messages to translate
        $toTranslateRequest = '';
        foreach ($this->messages as $originalMessage => $oldTranslation) {
            if (empty($oldTranslation)) {
                $toTranslateRequest .= '&q='.rawurlencode(str_replace(['{', '}'], ['<span class="notranslate">', '</span>'], $originalMessage));
            }
        }

        // If no empty translation
        if ($toTranslateRequest == '') {
            return;
        }

        // Create URL
        $url = $module->googleApiUrl.'?key=' . $module->googleApiKey . $toTranslateRequest . '&source=en&target='.strtolower(substr($this->language, 0, 2));

        // Ask Google API
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handle);
        $responseDecoded = json_decode($response, true);
        $responseCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        // Get translations
        if($responseCode != 200 || !isset($responseDecoded['data']['translations'])) {
            return;
        }
        $translations = $responseDecoded['data']['translations'];

        // Replace empty translations
        $i = 0;
        foreach ($this->messages as $originalMessage => $oldTranslation) {
            if (empty($oldTranslation)) {
                if (!empty($translations[$i]['translatedText'])) {
                   $this->messages[$originalMessage] = str_replace(['<span class="notranslate">', '</span>'], ['{', '}'], $translations[$i]['translatedText']);
                }
                $i++;
            }
        }
    }

    /**
     * @return bool
     * @throws \yii\base\Exception
     */
    public function save()
    {
        if(!$this->space || !$this->space->can(ManageTranslations::class)) {
            return false;
        }

        if (!$this->validate()) {
            return false;
        }

        if (empty($this->messages)) {
            return true;
        }

        $this->saveAndValidateTranslations();

        if (!$this->basePath->updateTranslations($this->language, $this->file, $this->messages)) {
            return false;
        }

        $this->loadMessages();
        return true;
    }

    private function saveAndValidateTranslations()
    {
        foreach ($this->translationLogs as $translationModel) {
            $translationModel->save();

            if ($translationModel->hasErrors('translation')) {
                $this->errors[$translationModel->getTID()] = $translationModel->getFirstError('translation');
            }

            if ($translationModel->wasPurified) {
                $this->warnings[$translationModel->getTID()] = Yii::t('TranslationModule.base', 'Your input has been purified from suspicious html.');
            }

            if (!$translationModel->hasErrors()) {
                $this->messages[$translationModel->message] = $translationModel->translation;
            }
        }
    }

    public function getTranslationFieldClass($message)
    {
        $tid = TranslationLog::tid($message);
        if (isset($this->errors[$tid])) {
            return 'has-error';
        }

        if (isset($this->warnings[$tid])) {
            return 'has-warning';
        }

        return '';
    }

    public function getHelpBlockMessage($message)
    {
        $tid = TranslationLog::tid($message);
        if (isset($this->errors[$tid])) {
            return $this->errors[$tid];
        }

        if (isset($this->warnings[$tid])) {
            return $this->warnings[$tid];
        }

        return null;
    }

    public function getModuleIdSelection()
    {
        $moduleIds = BasePath::getModuleIds();
        array_walk($moduleIds, function (&$value, $key) {
            $value = $this->isCoreModule($key) ? 'HumHub - ' . $value : $value = 'Module - ' . $value;
            $value .= ' (' . TranslationCoverage::getModuleCoverage(BasePath::getBasePath($key), $this->language) . '%)';
        });
        asort($moduleIds);
        return $moduleIds;
    }

    private function isCoreModule($moduleId)
    {
        if ($moduleId === BasePath::CORE_MODULE_ID) {
            return true;
        }

        try {
            $module = Yii::$app->moduleManager->getModule($moduleId);

            $basePath = $this->replaceSeperator($module->getBasePath());
            $humhubPath = $this->replaceSeperator(Yii::getAlias('@humhub'));

            return strpos($basePath, $humhubPath) !== false;
        } catch (\Exception $ex) {}

        return false;
    }

    protected function replaceSeperator($path)
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, Yii::getAlias($path));
    }

    /**
     * @return array
     */
    public function getFilesSelection()
    {
        $result = [];
        foreach ($this->files as $file) {
            $baseName = $file->getBaseName();
            $coverage = TranslationCoverage::getFileCoverage($file, $this->language);
            if ($coverage !== false) {
                $result[$baseName] = $baseName . "($coverage%)";
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getLanguageSelection()
    {
        $result = [];
        foreach (Languages::getTranslatableUserLanguages() as $language) {
            $result[$language] = ($this->language === $language)
                ? $language . ' (' . TranslationCoverage::getLanguageCoverage($language) . '%)'
                : $language;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function formName()
    {
        return '';
    }

    public function getMessageLanguage()
    {
        return $this->language;
    }

    public function getMessageModuleId()
    {
        return $this->moduleId;
    }

    public function getMessageBasename()
    {
        return $this->messageFile->getBaseName();
    }
}