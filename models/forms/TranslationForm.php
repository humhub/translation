<?php

namespace humhub\modules\translation\models\forms;

use humhub\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\modules\translation\models\BasePath;
use humhub\modules\translation\models\Languages;
use humhub\modules\translation\models\MessageFile;
use humhub\modules\translation\models\TranslationCoverage;
use humhub\modules\translation\models\TranslationFileIF;
use humhub\modules\translation\models\TranslationLog;
use humhub\modules\translation\Module;
use humhub\modules\translation\permissions\ManageTranslations;
use Yii;
use yii\base\Model;

class TranslationForm extends Model implements TranslationFileIF
{
    /**
     * Language codes supported by Google Translate (https://cloud.google.com/translate/docs/languages)
     */
    public const GOOGLE_TRANSLATE_SUPPORTED_LANGUAGES = ['af', 'sq', 'am', 'ar', 'hy', 'az', 'eu', 'be', 'bn', 'bs', 'bg', 'ca', 'ceb', 'zh', 'zh-CN', 'zh-TW', 'co', 'hr', 'cs', 'da', 'nl', 'en', 'eo', 'et', 'fi', 'fr', 'fy', 'gl', 'ka', 'de', 'el', 'gu', 'ht', 'ha', 'haw', 'iw', 'he', 'hi', 'hmn', 'hu', 'is', 'ig', 'id', 'ga', 'it', 'ja', 'jv', 'kn', 'kk', 'km', 'rw', 'ko', 'ku', 'ky', 'lo', 'la', 'lv', 'lt', 'lb', 'mk', 'mg', 'ms', 'ml', 'mt', 'mi', 'mr', 'mn', 'my', 'ne', 'no', 'ny', 'or', 'ps', 'fa', 'pl', 'pt', 'pa', 'ro', 'ru', 'sm', 'gd', 'sr', 'st', 'sn', 'sd', 'si', 'sk', 'sl', 'so', 'es', 'su', 'sw', 'sv', 'tl', 'tg', 'ta', 'tt', 'te', 'th', 'tr', 'tk', 'uk', 'ur', 'ug', 'uz', 'vi', 'cy', 'xh', 'yi', 'yo', 'zu'];

    /**
     * Correspondance between Humhub and Google translate language codes
     */
    public const HUMHUB_LANGUAGE_CODE_TO_GOOGLE_TRANSLATE = [
        'nb-NO' => 'no',
        'nn-NO' => 'no',
        'pt-BR' => 'pt',
        'fa-IR' => 'fa',
    ];

    /**
     * Maximum text queries that Google translate API can do in one HTTP request
     */
    public const GOOGLE_TRANSLATE_MAX_TEXT_QUERIES = 128;

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
    public $parentMessages;

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
            [['file'], 'validateFile'],
            [['file'], 'required' , 'message' => Yii::t('TranslationModule.base', 'The selected translation file could not be found.')],
            [['files'], 'required' , 'message' => Yii::t('TranslationModule.base', 'No translation files found for given selection.')],
            [['language', 'moduleId', 'file'], 'string'],
            [['language', 'moduleId'], 'required'],
        ];
    }

    public function getMessageSettingString($withFile = true)
    {
        return  ['settings' => '"' . Html::encode($this->moduleId) . ' / ' . Html::encode($this->language) . (($this->file && $withFile) ? ' / ' . Html::encode($this->file) : '') . '"'];
    }

    public function validateFile()
    {
        if (!$this->messageFile || !$this->messageFile->validate()) {
            $this->addError('file', Yii::t('TranslationModule.base', 'The message file for {settings} not found!', $this->getMessageSettingString()));
        }
    }

    public function validateLanguage()
    {
        if (!in_array($this->language, Languages::getTranslatableUserLanguages())) {
            $this->addError('language', 'You are not allowed to translate this language!');
        }
        if (!$this->messageFile || !$this->messageFile->validateLanguagePath($this->language)) {
            $this->addError('language', Yii::t('TranslationModule.base', 'The translation path for language {settings} could not be found!', $this->getMessageSettingString(false)));
        }
    }

    public function attributeLabels()
    {
        return [
            'moduleId' => Yii::t('TranslationModule.base', 'Module'),
            'language' => Yii::t('TranslationModule.base', 'Language'),
            'file' => Yii::t('TranslationModule.base', 'File'),
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
            $this->addError('space', Yii::t('TranslationModule.base', 'There is no language related space available for language {lang}', ['lang' => $this->language]));
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

            $parentLanguage = $this->getParentLanguage();
            if ($parentLanguage !== null && array_key_exists($parentLanguage, Yii::$app->i18n->getAllowedLanguages())) {
                $this->parentMessages = $this->messageFile->getMessages($parentLanguage);
            }
        }
    }


    /**
     * Translate automatically with Google translate API
     * $googleApiKey must be set
     * @param int $queryStart
     * @return void
     */
    protected function autoTranslateEmptyValues(int $queryStart = 1)
    {
        // Get target language code
        $targetLanguageCode = strtr((string)$this->language, static::HUMHUB_LANGUAGE_CODE_TO_GOOGLE_TRANSLATE);

        // Check if code is supported by Google translate
        if (!in_array($targetLanguageCode, static::GOOGLE_TRANSLATE_SUPPORTED_LANGUAGES)) {
            return;
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('translation');
        if (empty($module->googleApiKey)) {
            return;
        }

        // Get messages to translate
        $toTranslateRequest = '';
        $queryNumb = 0;
        foreach ($this->messages as $originalMessage => $oldTranslation) {
            if (empty($oldTranslation)) {
                $queryNumb++;
                if ($queryNumb < $queryStart) {
                    continue;
                }
                if ($queryNumb >= ($queryStart + static::GOOGLE_TRANSLATE_MAX_TEXT_QUERIES)) {
                    $this->autoTranslateEmptyValues($queryNumb);
                    break;
                }
                $toTranslateRequest .= '&q=' . rawurlencode(str_replace(['{', '}'], ['<span class="notranslate">', '</span>'], $originalMessage));
            }
        }

        // If no empty translation
        if ($toTranslateRequest == '') {
            return;
        }

        // Build URL
        $query = [
            'key' => $module->googleApiKey,
            'source' => 'en',
            'target' => $targetLanguageCode,
        ];
        $url = $module->googleApiUrl . '?' . http_build_query($query) . $toTranslateRequest;

        // Ask Google API
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handle);
        $responseDecoded = json_decode($response, true);
        $responseCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        // Get translations
        if (!isset($responseDecoded['data']['translations'])) {
            Yii::error('Translation module - autoTranslateEmptyValues error code ' . $responseCode . ' for URL ' . $url);
            return;
        }
        $translations = $responseDecoded['data']['translations'];

        // Replace empty translations
        $queryNumb = 0;
        $resultNumb = 0;
        foreach ($this->messages as $originalMessage => $oldTranslation) {
            if (empty($oldTranslation)) {
                $queryNumb++;
                if ($queryNumb < $queryStart) {
                    continue;
                }
                if ($queryNumb >= ($queryStart + static::GOOGLE_TRANSLATE_MAX_TEXT_QUERIES)) {
                    break;
                }
                if (!empty($translations[$resultNumb]['translatedText'])) {
                    $this->messages[$originalMessage] = htmlspecialchars_decode(str_replace(['<span class="notranslate">', '</span>', '&#39;'], ['{', '}', '\''], $translations[$resultNumb]['translatedText']));
                }
                $resultNumb++;
            }
        }
    }

    public function canManage(): bool
    {
        return $this->space instanceof Space
            && $this->space->can(ManageTranslations::class);
    }

    /**
     * @return bool
     * @throws \yii\base\Exception
     */
    public function save()
    {
        if (!$this->canManage()) {
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
                $this->warnings[$translationModel->getTID()] = Yii::t('TranslationModule.base', 'Your input has been purified.');
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
            return 'is-invalid';
        }

        if (isset($this->warnings[$tid])) {
            return 'is-invalid';
        }

        return '';
    }

    public function getHelpBlockMessage($message)
    {
        $tid = TranslationLog::tid($message);

        return $this->errors[$tid] ?? $this->warnings[$tid] ?? null;
    }

    public function getModuleIdSelection()
    {
        $moduleIds = BasePath::getModuleIds();
        array_walk($moduleIds, function (&$value, $key): void {
            $value = $this->isCoreModule($key) ? 'HumHub - ' . $value : $value = 'Module - ' . $value;

            $coverage =  TranslationCoverage::getModuleCoverage(BasePath::getBasePath($key), $this->language);

            if ($coverage === false) {
                $value .= ' (?)';
            } else {
                $value .= ' (' . $coverage . '%)';
            }


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

            return str_contains((string) $basePath, (string) $humhubPath);
        } catch (\Exception) {
        }

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

    public function getParentLanguage(): ?string
    {
        return preg_match('/^(.+?)-(.+)$/i', $this->language, $m) ? $m[1] : null;
    }
}
