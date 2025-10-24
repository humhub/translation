<?php

namespace humhub\modules\translation\models;

use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use Yii;
use yii\base\BaseObject;

/**
 * Helper class used to cache and access available languages and space relations.
 *
 * @package humhub\modules\translation\models
 */
class Languages extends BaseObject
{
    /**
     * @var []
     */
    private static $languages;

    /**
     * @var []
     */
    private static $userLanguages;

    /**
     * @var []
     */
    private static $skipLanguages = [
        'en-US',
        'en-GB',
    ];

    /**
     * Returns an array of all available language codes
     * @return array
     */
    public static function getAllAvailableLanguages()
    {
        return array_keys(Yii::$app->params['availableLanguages']);
    }

    /**
     * Returns an array with all translatable language codes
     * @var []
     */
    public static function getAllTranslatableLanguages()
    {
        if (!static::$languages) {
            static::$languages = array_filter(static::getAllAvailableLanguages(), fn($lang) => !in_array($lang, static::$skipLanguages, true));

            sort(static::$languages);
        }

        return static::$languages;
    }

    /**
     * Returns an array of language codes the user is allowed to translate. System administrators can access all
     * languages.
     *
     * @var []
     */
    public static function getTranslatableUserLanguages()
    {
        if (!static::$userLanguages) {
            static::$userLanguages = (Yii::$app->request->isConsoleRequest || Yii::$app->user->isAdmin())
                ? static::getAllTranslatableLanguages()
                : static::filterUserLanguages();
        }

        return static::$userLanguages;
    }

    public static function flush()
    {
        static::$userLanguages = null;
        static::$languages = null;
    }

    /**
     * Filters out languages not related to the current user.
     * A user needs to be member of the space related to a language.
     *
     * @param $allLanguages
     * @return array
     */
    private static function filterUserLanguages()
    {
        $userLanguages = [];
        $allLanguages = static::getAllTranslatableLanguages();

        foreach (Membership::GetUserSpaces() as $space) {
            $spaceLanguage = static::getLanguageBySpace($space);
            if (in_array($spaceLanguage, $allLanguages, true)) {
                $userLanguages[] = $spaceLanguage;
            }
        }

        return $userLanguages;
    }

    /**
     * Translates a space name to related language code
     *
     * @param Space $space
     * @return string
     */
    private static function getLanguageBySpace(Space $space)
    {
        if (str_contains($space->name, '-')) {
            [$lang, $ter] = explode('-', $space->name, 2);
            return strtolower($lang) . '-' . strtoupper($ter);
        }
        return strtolower($space->name);
    }

    /**
     * @param $language
     * @return Space|null
     */
    public static function findSpaceByLanguage($language)
    {
        $spaceName = static::getSpaceNameByLanguage($language);

        if (!$spaceName) {
            return null;
        }

        return Space::findOne(['name' => strtoupper((string) $spaceName)]);
    }

    public static function getLanguageBySpaceName($spaceName)
    {
        if ($spaceName instanceof Space) {
            $spaceName = $spaceName->name;
        }

        $language = $spaceName;

        if (str_contains((string) $language, '-')) {
            [$lang, $ter] = explode('-', (string) $spaceName, 2);
            $language = strtolower($lang) . '-' . strtoupper($ter);
        } else {
            $language = strtolower((string) $language);
        }

        if (!static::isValidLanguage($language)) {
            return null;
        }

        return $language;
    }

    public static function getSpaceNameByLanguage($language)
    {
        if (!static::isValidLanguage($language)) {
            return null;
        }

        if (str_contains((string) $language, '-')) {
            [$lang, $ter] = explode('-', (string) $language, 2);
            $spaceName = strtoupper($lang) . '-' . strtoupper($ter);
        } else {
            $spaceName = strtoupper((string) $language);
        }

        return $spaceName;
    }

    public static function getDefaultLanguage()
    {
        if (static::isLanguageAvailable()) {
            return array_values(static::getTranslatableUserLanguages())[0];
        }

        return null;
    }

    public static function isLanguageAvailable($lang = null)
    {
        $availableLanguages = static::getTranslatableUserLanguages();

        if (empty($availableLanguages)) {
            return false;
        }

        if ($lang) {
            return in_array($lang, $availableLanguages, true);
        }

        return !empty($availableLanguages);
    }

    public static function isValidLanguage($lang)
    {
        return in_array($lang, static::getAllTranslatableLanguages());
    }
}
