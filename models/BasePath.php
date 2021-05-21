<?php


namespace humhub\modules\translation\models;

use Yii;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use humhub\modules\file\libs\FileHelper;
use yii\base\Module;

class BasePath extends TranslationPath
{
    /**
     * @var static[]
     */
    private static $basePaths = [];

    /**
     * @var MessageFile[]
     */
    private $messageFiles = [];

    public function rules()
    {
        return array_merge([
            ['moduleId', 'validatePath'],
        ], parent::rules());
    }

    public function validatePath()
    {
        if(!is_dir($this->getPath())) {
            $this->addError('moduleId', 'Module does not have a message base path.');
        }
    }

    /**
     * @param $moduleId
     * @return static
     */
    public static function getBasePath($moduleId)
    {
        if(!isset(static::$basePaths[$moduleId])) {
            static::$basePaths[$moduleId] = new static(['moduleId' => $moduleId]);
        }

        return static::$basePaths[$moduleId];
    }

    /**
     * @param $file
     * @return MessageFile
     */
    public function getMessageFile($file)
    {
        $file = basename($file, '.php').'.php';

        if(!isset($this->messageFiles[$file])) {
            $this->messageFiles[$file] = new MessageFile(['basePath' => $this, 'file' => $file]);
        }

        return  $this->messageFiles[$file];
    }

    /**
     * Returns the base path for a given module id and language. If no language is given the root message path
     * of this modules is returned.
     *
     * @param string $moduleId
     * @param string|null $language
     * @return bool|string
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function getPath($language = null)
    {
        if($this->isCoreModulePath()) {
            return $language
                ? Yii::getAlias('@humhub/messages/' . $language)
                : Yii::getAlias('@humhub/messages');
        }

        if($language && !static::validateLanguage($language)) {
            return null;
        }

        try {
            $module = $this->getModule();
        } catch(\Exception $e) {
            return null;
        }

        if(!$module) {
            return null;
        }

        $moduleId = $this->moduleId;
        $basePath = $this->getModuleMessageBasePath($module);

        if (!$language) {
            return is_dir($basePath) ? $basePath : null;
        }

        $languagePath = $this->getModuleMessageLanguagePath($module, $language);
        if (is_dir($languagePath)) {
            return $languagePath;
        }

        $legacyLanguagePath = $this->getLegacyModuleMessageLanguagePath($module, $language);
        if (is_dir($legacyLanguagePath)) {
            Yii::warning("Detected usage of legacy language path for module: '$moduleId' language: '$language'");
            return $legacyLanguagePath;
        }

        return null;
    }

    /**
     * @param $language
     * @return MessageFile[]
     * @throws Exception
     */
    public function getMessageFiles($language)
    {
        if(!$this->validateLanguagePath($language)) {
            return [];
        }

        /**
         * TODO: we should cache message files independently of language, since all languages should contain the same language files
         * if this is not the case this is rather an error
         */
        $files = FileHelper::findFiles($this->getPath($language), ['only' => ['*.php'], 'recursive' => false]);
        sort($files);

        $result = [];
        foreach ($files as $file) {
            $messageFile = $this->getMessageFile(basename( $file, '.php').'.php');
            if($messageFile->validate() && $messageFile->validateLanguagePath($language)) {
                $result[] = $messageFile;
            }
        }

        return $result;
    }

    /**
     * Returns a list of available modules
     */
    public static function getModuleIds(): array
    {
        $modules = ['core' => 'core'];
        foreach (Yii::$app->moduleManager->getModules(['includeCoreModules' => true]) as $moduleId => $def) {
            $messagePath = BasePath::getBasePath($moduleId);
            if ($messagePath->validate()) {
                $modules[$moduleId] = $moduleId;
            } else {
                Yii::warning("Invalid message path of module $moduleId detected.");
            }
        }

        asort($modules);
        return $modules;
    }

    protected function getModuleMessageBasePath(Module $module)
    {
        return $module->getBasePath() . DIRECTORY_SEPARATOR . 'messages';
    }

    private function getModuleMessageLanguagePath(Module $module, $language)
    {
        return $this->getModuleMessageBasePath($module) . DIRECTORY_SEPARATOR . $language;
    }

    private function getLegacyModuleMessageLanguagePath(Module $module, $language)
    {
        return $this->getModuleMessageLanguagePath($module, static::toLegacyLanguageCode($language));
    }

    public function updateTranslations($language, $file, $messages)
    {
        return $this->getMessageFile($file)->updateTranslations($language, $messages);
    }
}