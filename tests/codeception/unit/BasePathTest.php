<?php

namespace humhub\modules\translation\tests\codeception\unit\reminder;

use humhub\modules\translation\models\BasePath;
use translation\TranslationTest;

class BasePathTest extends TranslationTest
{
    public function testBasePath()
    {
        $path = BasePath::getBasePath('translation');
        $this->assertEqualAlias($path->getPath(), '@translation/messages');
        $this->assertTrue($path->validate());
    }

    public function testLanguageBasePath()
    {
        $path = BasePath::getBasePath('translation');
        $this->assertEqualAlias($path->getPath('de'), '@translation/messages/de');
        $this->assertTrue($path->validateLanguagePath('de'));
    }

    public function testValidateLanguage()
    {
        $path = BasePath::getBasePath('translation');

        $this->assertNull($path->getPath('../'));
        $this->assertFalse($path->validateLanguagePath('../'));
        $this->assertFalse($path->validateLanguage('../'));

        $this->assertNull($path->getPath('xy'));
        $this->assertFalse($path->validateLanguagePath('xy'));
        $this->assertFalse($path->validateLanguage('xy'));

        $this->assertTrue($path->validateLanguagePath('de'));
        $this->assertTrue($path->validateLanguage('de'));

        $this->assertTrue($path->validateLanguagePath('nb-NO'));
        $this->assertTrue($path->validateLanguage('nb-NO'));

        $this->assertNull($path->getMessageFile('../../xx.php')->getPath('de'));
    }

    public function testNonExistingModulePath()
    {
        $path = BasePath::getBasePath('nonexisting');
        $this->assertNull($path->getPath());
        $this->assertFalse($path->validate());
        $this->assertFalse($path->validateLanguagePath('de'));
    }
}