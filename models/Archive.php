<?php


namespace humhub\modules\translation\models;


use Yii;
use yii\helpers\Json;

class Archive
{
    public static $basePath = '@translation/messages';

    public static function load($lang)
    {
        $archiveFile = static::getFilePath($lang);
        if (is_file($archiveFile)) {
            return Json::decode(file_get_contents($archiveFile));
        }

        return [];
    }

    public static function validateBasePath($lang)
    {
        return is_dir((string)static::getBasePath($lang));
    }

    public static function getBasePath($lang)
    {
        return Yii::getAlias(static::$basePath . DIRECTORY_SEPARATOR . $lang);
    }

    public static function getFilePath($lang)
    {
        return Yii::getAlias(static::$basePath . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . 'archive.json');
    }

    public static function update($lang, $messages)
    {
        if (!self::validateBasePath($lang)) {
            return false;
        }

        file_put_contents(static::getFilePath($lang), Json::encode($messages));

        return true;
    }

}