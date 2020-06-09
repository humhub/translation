<?php

namespace humhub\modules\translation\tests\codeception\unit\reminder;

use humhub\modules\translation\models\BasePath;
use tests\codeception\_support\HumHubDbTestCase;
use translation\TranslationTest;
use Yii;

class MessageFileTest extends TranslationTest
{
    public function testGetMessageFiles()
    {
        $path = BasePath::getBasePath('translation');
        $files = $path->getMessageFiles('de');

        $this->assertEquals('base.php', $files[0]->getFileName());
        $this->assertEquals('base', $files[0]->getBaseName());
        $this->assertEqualAlias($files[0]->getPath('de'), '@translation/messages/de/base.php');

        $this->assertEquals('test.php', $files[1]->getFileName());
        $this->assertEquals('test', $files[1]->getBaseName());
        $this->assertEqualAlias($files[1]->getPath('de'), '@translation/messages/de/test.php');

        $this->assertNull($files[0]->getPath('../'));

        $this->assertEquals('views_translate_index.php', $files[2]->getFileName());
        $this->assertEquals('views_translate_index', $files[2]->getBaseName());
        $this->assertEqualAlias($files[2]->getPath('de'), '@translation/messages/de/views_translate_index.php');
    }
}