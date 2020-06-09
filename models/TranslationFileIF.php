<?php


namespace humhub\modules\translation\models;


interface TranslationFileIF
{
    public function getMessageLanguage();
    public function getMessageModuleId();
    public function getMessageBasename();

}