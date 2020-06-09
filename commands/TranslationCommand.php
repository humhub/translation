<?php


namespace humhub\modules\translation\commands;


use Yii;

class TranslationCommand
{
    protected static function log($msg)
    {
        if(Yii::$app->request->isConsoleRequest) {
            print $msg . "\n";
        }
    }
}