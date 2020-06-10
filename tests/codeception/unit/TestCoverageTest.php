<?php

namespace humhub\modules\translation\tests\codeception\unit;

use humhub\modules\translation\models\BasePath;
use humhub\modules\translation\models\TranslationCoverage;
use translation\TranslationTest;

class TestCoverageTest extends TranslationTest
{

    public function testNoFileCoverage()
    {
        $this->setTrnaslationState(
            [
                'de' => [
                    'Test' => ''
                ]
            ]
        );
        $basePath = BasePath::getBasePath('translation');
        $this->assertEquals(0, TranslationCoverage::getFileCoverage($basePath->getMessageFile('test'), 'de'));
    }

    public function testFileCoverage()
    {
        $this->setTrnaslationState(
            [
                'de' => [
                    'Test1' => 'Übersetzt1',
                    'Test2' => 'Übersetzt2',
                    'Test3' => '',
                    'Test4' => '',
                ]
            ]
        );
        $basePath = BasePath::getBasePath('translation');
        $this->assertEquals(50, TranslationCoverage::getFileCoverage($basePath->getMessageFile('test'), 'de'));
    }

    public function testFullFileCoverage()
    {
        $this->setTrnaslationState(
            [
                'de' => [
                    'Test1' => 'Übersetzt1',
                    'Test2' => 'Übersetzt2',
                ]
            ]
        );
        $basePath = BasePath::getBasePath('translation');
        $this->assertEquals(100, TranslationCoverage::getFileCoverage($basePath->getMessageFile('test'), 'de'));
    }

    public function testModuleCoverage()
    {
        $this->setTrnaslationState(
            [
                'de' => [
                    'Test1' => 'Übersetzt1',
                    'Test2' => 'Übersetzt2',
                ]
            ]
        );
        $basePath = BasePath::getBasePath('translation');
        $this->assertEquals(100, TranslationCoverage::getModuleCoverage($basePath, 'de'));
    }

    public function testEmptyFileCoverage()
    {
        $this->setTrnaslationState(
            [
                'de' => []
            ]
        );
        $basePath = BasePath::getBasePath('translation');
        $this->assertFalse(TranslationCoverage::getFileCoverage($basePath->getMessageFile('test'), 'de'));
    }


}