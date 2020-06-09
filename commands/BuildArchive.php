<?php


namespace humhub\modules\translation\commands;

use humhub\modules\translation\models\Archive;
use humhub\modules\translation\models\BasePath;
use humhub\modules\translation\models\Languages;
use yii\helpers\Json;

/**
 * This command is responsible for building translation archive files. The translation archive is a
 * json file containing all old and new translations for all available modules.
 *
 * @see Archive
 */
class BuildArchive extends TranslationCommand
{
    /**
     * Updates or creates the translation archive file for the given language, or all languages if no $language parameter is
     * given.
     *
     * @param $language
     * @throws \yii\base\Exception
     */
    public static function run($language = null, $module = null)
    {
        $languages = $language ? [$language] : Languages::getAllAvailableLanguages();

        foreach ($languages as $lang) {
            static::log("Processing $lang ...");

            if(!Archive::validateBasePath($lang)) {
                static::log( "Skipped (No message folder): ".Archive::getBasePath($lang));
            }

            $archive = Archive::load($lang);

            $moduleIds = $module ? [$module] : BasePath::getModuleIds();

            foreach ($moduleIds as $moduleId) {
                $basePath = BasePath::getBasePath($moduleId);
                if($basePath->validateLanguagePath($lang)) {
                    foreach ($basePath->getMessageFiles($lang) as $messageFile) {
                        foreach ($messageFile->getMessages($lang) as $original => $translated) {

                            $translated = static::cutUnusedMarking($translated);

                            if (!empty($translated)) {
                                if (isset($archive[$original]) && !in_array($translated, $archive[$original])) {
                                    $archive[$original][] = $translated;
                                } else {
                                    $archive[$original] = [$translated];
                                }
                            }
                        }
                    }
                }
            }

            // Save
            if(Archive::update($lang, $archive)) {
                static::log("Saved!");
            } else {
                static::log("Could not save archive!");
            }
        }
    }

    private static function cutUnusedMarking($translated)
    {
        if (substr($translated, 0, 2) === '@@' && substr($translated, -2, 2) === '@@') {
            $translated = preg_replace('/^@@/', '', $translated);
            $translated = preg_replace('/@@$/', '', $translated);
        }

        return $translated;
    }
}