<?php

namespace humhub\modules\translation\models;

/**
 * Helper class for calculating the translation coverage in percent of a given language on
 *
 *  - module level
 *  - overall language level
 *  - file level
 *
 * @package humhub\modules\translation\models
 */
class TranslationCoverage
{
    /**
     * Calculates the translation coverage in percent of a given message file and language
     *
     * Returns false if there are no messages to translate
     *
     * @param MessageFile $file
     * @param $language
     * @return float|int|bool
     */
    public static function getFileCoverage(MessageFile $file, $language)
    {
        return static::calculateCoverage($file->getMessages($language));
    }

    /**
     * Calculates the translation coverage in percent of a given module and language
     *
     * Returns false if there are no messages to translate
     *
     * @param BasePath $basePath
     * @param $language
     * @return float|int|bool
     */
    public static function getModuleCoverage(BasePath $basePath, $language)
    {
        return static::calculateCoverage(static function () use ($basePath, $language) {
            foreach ($basePath->getMessageFiles($language) as $messageFile) {
                yield $messageFile->getMessages($language);
            }
        });
    }

    /**
     * Calculates the total translation coverage in percent of a given language
     *
     * Returns false if there are no messages to translate
     *
     * @param $language
     * @return float|int
     */
    public static function getLanguageCoverage($language)
    {
        return static::calculateCoverage(static function () use ($language) {
            foreach (BasePath::getModuleIds() as $moduleId) {
                $basePath = BasePath::getBasePath($moduleId);
                foreach ($basePath->getMessageFiles($language) as $file) {
                    yield $file->getMessages($language);
                }
            }
        });
    }

    /**
     * Helper function to calculate test coverage in percent. Accepts either an array of translations or a generator
     * function which should yield arrays of translations.
     *
     * Returns false if there are no messages to translate
     *
     * @param $generator
     * @return float|int|bool
     */
    private static function calculateCoverage($generator)
    {
        $countTotal = 0;
        $countTranslated = 0;

        $generator = is_array($generator) ? [$generator] : $generator();

        foreach ($generator as $messages) {
            $countTranslated += count(array_filter($messages));
            $countTotal += count($messages);
        }

        return $countTotal ? floor($countTranslated * 100 / $countTotal) : false;
    }

}
