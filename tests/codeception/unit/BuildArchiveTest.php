<?php

namespace humhub\modules\translation\tests\codeception\unit\reminder;

use humhub\modules\translation\commands\BuildArchive;
use humhub\modules\translation\models\Archive;
use translation\TranslationTest;

class BuildArchiveTest extends TranslationTest
{
    const TEST_MESSAGE = 'Test building an archive file';

    public $initialTranslationSate = [
        'de' => [
            self::TEST_MESSAGE => 'Teste die Bildung eines Archivefiles',
        ]
    ];

    public function _before()
    {
        parent::_before();

        $archive = Archive::load('de');
        unset($archive['Test building an archive file']);
        Archive::update('de', $archive);
    }

    public function testUpdateArchive()
    {
        $archive = Archive::load('de');
        $this->assertFalse(isset($archive[self::TEST_MESSAGE]));

        BuildArchive::run('de', 'translation');

        $updatedArchive = Archive::load('de');
        $this->assertTrue(isset($updatedArchive[self::TEST_MESSAGE]));
        $this->assertCount(1, $updatedArchive[self::TEST_MESSAGE]);
    }
}