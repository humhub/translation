<?php

namespace humhub\modules\translation\tests\codeception\unit;

use humhub\modules\translation\models\BasePath;
use translation\TranslationTest;

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
    }
}
