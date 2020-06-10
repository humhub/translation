<?php

namespace humhub\modules\translation\tests\codeception\unit;

use humhub\modules\space\models\Space;
use humhub\modules\translation\models\forms\TranslationForm;
use humhub\modules\translation\models\Languages;
use humhub\modules\translation\models\TranslationLog;
use translation\TranslationTest;
use Yii;

class LanguagesTest extends TranslationTest
{
    public function _before()
    {
        parent::_before();
        Languages::flush();
    }

    public function testAdminLanguages()
    {
        $this->becomeUser('Admin');

        Yii::$app->request->setIsConsoleRequest(false);
        $translatableLangs = Languages::getTranslatableUserLanguages();

        $this->assertEquals($translatableLangs, Languages::getAllTranslatableLanguages());
        Yii::$app->request->setIsConsoleRequest(true);
    }

    public function testUserLanguages()
    {
        $this->becomeUser('User2'); // Is member of de space

        Yii::$app->request->setIsConsoleRequest(false);
        $translatableLangs = Languages::getTranslatableUserLanguages();

        $this->assertEquals($translatableLangs, ['de']);
        Yii::$app->request->setIsConsoleRequest(true);
    }

    public function testUserHasNoLanguages()
    {
        $this->becomeUser('User1'); // Is member of de space

        Yii::$app->request->setIsConsoleRequest(false);
        $translatableLangs = Languages::getTranslatableUserLanguages();

        $this->assertEmpty($translatableLangs);
        Yii::$app->request->setIsConsoleRequest(true);
    }

}