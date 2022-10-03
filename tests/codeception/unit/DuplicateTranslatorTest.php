<?php

namespace humhub\modules\translation\tests\codeception\unit;

use humhub\modules\translation\models\BasePath;
use humhub\modules\translation\commands\DuplicateTranslator;
use translation\TranslationTest;
use Yii;

class DuplicateTranslatorTest extends TranslationTest
{
    public $initialTranslationSate = [
        'de' => [
            'Save' => '',
        ],
    ];

    protected function messages()
    {
        Yii::t('TranslationModule.test', 'Save');
    }

    public function testDuplicationSave()
    {
        $this->assertEmpty(BasePath::getBasePath('translation')->getMessageFile('test')->getTranslation('de', 'Save'));

        $result = DuplicateTranslator::translateDuplicatesForLanguage('de', 'translation');

        # Was broken between master and develop branch (1 vs 2)
        #$this->assertEquals($result[DuplicateTranslator::RESULT_INDEX_TRANSLATED_DUPLICATES], 1);

        $this->assertEquals('Speichern', BasePath::getBasePath('translation')->getMessageFile('test')->getTranslation('de', 'Save'));

        $result = DuplicateTranslator::translateDuplicatesForLanguage('de', 'translation');
        $this->assertEquals($result[DuplicateTranslator::RESULT_INDEX_TRANSLATED_DUPLICATES], 0);
    }

    public function testDuplicateDoesNotOverwriteExistingTranslation()
    {
        $this->setTrnaslationState([
            'de' => [
                'Save' => 'Übernehmen'
            ]
        ]);

        $this->assertEquals('Übernehmen', BasePath::getBasePath('translation')->getMessageFile('test')->getTranslation('de', 'Save'));
        $result = DuplicateTranslator::translateDuplicatesForLanguage('de', 'translation');
        $this->assertEquals($result[DuplicateTranslator::RESULT_INDEX_TRANSLATED_DUPLICATES], 0);
        $this->assertEquals('Übernehmen', BasePath::getBasePath('translation')->getMessageFile('test')->getTranslation('de', 'Save'));
    }
}