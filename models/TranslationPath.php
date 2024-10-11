<?php

namespace humhub\modules\translation\models;

use Yii;
use yii\base\Model;
use yii\base\Module;

/**
 * Class TranslationPath represents a filesystem path within the translation system. Instances of this model
 * may not be bound to a specific language.
 *
 * @package humhub\modules\translation\models
 */
abstract class TranslationPath extends Model
{
    public const CORE_MODULE_ID = 'core';

    /**
     * @var Module
     */
    protected $module;

    /**
     * @var string
     */
    public $moduleId;

    /**
     * Returns the message path for a given language
     *
     * @param string $moduleId
     * @param string|null $language
     * @return string
     */
    abstract public function getPath($language = null);


    public function rules()
    {
        return [
            ['moduleId', 'required'],
            ['moduleId', 'validateModule'],
        ];
    }

    public function validateModule()
    {
        if (!$this->getModule()) {
            $this->addError('moduleId', 'Module not found!');
        }
    }

    public function validateLanguagePath($language)
    {
        return is_dir((string)$this->getPath($language));
    }

    public static function validateLanguage($language)
    {
        return array_key_exists($language, Yii::$app->params['availableLanguages']);
    }

    /**
     * @return Module
     */
    public function getModule()
    {
        if (!$this->module) {
            $this->module = $this->isCoreModulePath()
                ? Yii::$app
                : Yii::$app->moduleManager->getModule($this->moduleId);
        }

        return $this->module;
    }

    public function isCoreModulePath()
    {
        return $this->moduleId === static::CORE_MODULE_ID;
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
        return $this->getModuleMessageLanguagePath($module, strtolower(str_replace('-', '_', $language)));
    }

    public static function toLegacyLanguageCode($language)
    {
        return strtolower(str_replace('-', '_', $language));
    }

}
