<?php

namespace humhub\modules\translation\tests\codeception\unit;

use humhub\modules\translation\models\forms\TranslationForm;
use humhub\modules\translation\models\TranslationLog;
use translation\TranslationTest;
use Yii;

class MessageValidationTest extends TranslationTest
{

    public $initialTranslationSate = [
        'de' => [
            self::TEST_MESSAGE => '',
            'Test with {parameter}' => '',
            'Today is {0,date}' => '',
            'Balance: {0,number}' => '',
            'Maximum of {n,plural,=1{# space} other{# spaces}}' => '',
            'Maximum {parameter} of {n,plural,=1{# space} other{# spaces}}' => '',
            '{count} {n,plural,=1{day} other{days}}' => ''
        ]
    ];

    /**
     * Empty translations should not overwrite existing translations.
     *
     * @throws \yii\base\Exception
     */
    public function testSaveFileEmptyMessage()
    {
        $this->becomeUser('Admin');
        $form = new TranslationForm();
        $this->assertTrue($form->load([
            'moduleId' => 'translation',
            'language' => 'de',
            'file' => 'test',
            $this->testMessageTID => 'Dies ist ein test'
        ]));

        $this->assertTrue($form->save());

        $history = TranslationLog::findHistory($form->messageFile, 'de', static::TEST_MESSAGE)->all();
        $this->assertCount(1, $history);
        $this->assertEquals('de', $history[0]->language);
        $this->assertEquals('test', $history[0]->file);
        $this->assertEquals(static::TEST_MESSAGE, $history[0]->message);
        $this->assertEquals('Dies ist ein test', $history[0]->translation);
        $this->assertEquals(Yii::$app->user->id, $history[0]->getTranslator()->id);
        $this->assertEmpty($history[0]->translation_old);


        $form = new TranslationForm();
        $this->assertTrue($form->load([
            'moduleId' => 'translation',
            'language' => 'de',
            'file' => 'test',
            $this->testMessageTID => 'Dies ist ein test!'
        ]));

        $this->assertTrue($form->save());

        $history = TranslationLog::findHistory($form->messageFile, 'de',static::TEST_MESSAGE)->all();
        $this->assertCount(2, $history);
        $this->assertEquals('de', $history[0]->language);
        $this->assertEquals('test', $history[0]->file);
        $this->assertEquals(static::TEST_MESSAGE, $history[0]->message);
        $this->assertEquals('Dies ist ein test!', $history[0]->translation);
        $this->assertEquals('Dies ist ein test', $history[0]->translation_old);
    }

    public function testSaveMessageValidParameter()
    {
        $this->becomeUser('Admin');
        $form = new TranslationForm();
        $this->assertTrue($form->load([
            'moduleId' => 'translation',
            'language' => 'de',
            'file' => 'test',
            TranslationLog::tid('Test with {parameter}') => 'Test mit {parameter}'
        ]));

        $this->assertTrue($form->save());
        $this->assertEmpty($form->errors);
        $this->assertEmpty($form->warnings);
        $this->assertEquals($form->messageFile->getTranslation('de', 'Test with {parameter}'),  'Test mit {parameter}');
    }

    public function testInvalidParameterName()
    {
        $this->becomeUser('Admin');
        $form = new TranslationForm();
        $this->assertTrue($form->load([
            'moduleId' => 'translation',
            'language' => 'de',
            'file' => 'test',
            TranslationLog::tid('Test with {parameter}') => 'Test mit {xxx}'
        ]));

        $this->assertTrue($form->save());
        $this->assertNotEmpty($form->errors[TranslationLog::tid('Test with {parameter}')]);
        $this->assertEmpty($form->messageFile->getTranslation('de', 'Test with {parameter}'));
    }

    public function testAdditionalParameter()
    {
        $this->becomeUser('Admin');
        $form = new TranslationForm();
        $this->assertTrue($form->load([
            'moduleId' => 'translation',
            'language' => 'de',
            'file' => 'test',
            TranslationLog::tid('Test with {parameter}') => 'Test mit {parameter} {invalid}'
        ]));

        $this->assertTrue($form->save());
        $this->assertNotEmpty($form->errors[TranslationLog::tid('Test with {parameter}')]);
        $this->assertEmpty($form->messageFile->getTranslation('de', 'Test with {parameter}'));
    }

    public function testPluralParameterValid()
    {
        $this->becomeUser('Admin');
        $form = new TranslationForm();
        $this->assertTrue($form->load([
            'moduleId' => 'translation',
            'language' => 'de',
            'file' => 'test',
            TranslationLog::tid('Maximum {parameter} of {n,plural,=1{# space} other{# spaces}}') => 'Maximal {parameter} {n,plural,=1{# Space} other{# Spaces}}'
        ]));

        $this->assertTrue($form->save());
        $this->assertEmpty($form->errors);
        $this->assertEquals('Maximal {parameter} {n,plural,=1{# Space} other{# Spaces}}', $form->messageFile->getTranslation('de', 'Maximum {parameter} of {n,plural,=1{# space} other{# spaces}}'));
    }

    public function testPluralParameterInvalid1()
    {
        $this->becomeUser('Admin');
        $form = new TranslationForm();
        $this->assertTrue($form->load([
            'moduleId' => 'translation',
            'language' => 'de',
            'file' => 'test',
            TranslationLog::tid('Maximum of {n,plural,=1{# space} other{# spaces}}') => 'Maximal {n,plural,=1{# Space} andere{# Spaces}}'
        ]));

        $this->assertTrue($form->save());
        $this->assertNotEmpty($form->errors[TranslationLog::tid('Maximum of {n,plural,=1{# space} other{# spaces}}')]);
        $this->assertEmpty($form->messageFile->getTranslation('de', 'Maximum of {n,plural,=1{# space} other{# spaces}}'));
    }

    public function testPluralParameterInvalid2()
    {
        $this->becomeUser('Admin');
        $form = new TranslationForm();
        $this->assertTrue($form->load([
            'moduleId' => 'translation',
            'language' => 'de',
            'file' => 'test',
            TranslationLog::tid('Maximum of {n,plural,=1{# space} other{# spaces}}') => 'Maximal {n,mehrzahl,=1{# Space} other{# Spaces}}'
        ]));

        $this->assertTrue($form->save());
        $this->assertNotEmpty($form->errors[TranslationLog::tid('Maximum of {n,plural,=1{# space} other{# spaces}}')]);
        $this->assertEmpty($form->messageFile->getTranslation('de', 'Maximum of {n,plural,=1{# space} other{# spaces}}'));
    }

    public function testParameterSelectionInvalid3()
    {
        $this->becomeUser('Admin');
        $form = new TranslationForm();
        $this->assertTrue($form->load([
            'moduleId' => 'translation',
            'language' => 'de',
            'file' => 'test',
            TranslationLog::tid('Maximum of {n,plural,=1{# space} other{# spaces}}') => 'Maximal {x,mehrzahl,=1{# Space} other{# Spaces}}'
        ]));

        $this->assertTrue($form->save());
        $this->assertNotEmpty($form->errors[TranslationLog::tid('Maximum of {n,plural,=1{# space} other{# spaces}}')]);
        $this->assertEmpty($form->messageFile->getTranslation('de', 'Maximum of {n,plural,=1{# space} other{# spaces}}'));
    }

    public function testMissingParameter1()
    {
        $this->becomeUser('Admin');
        $form = new TranslationForm();
        $this->assertTrue($form->load([
            'moduleId' => 'translation',
            'language' => 'de',
            'file' => 'test',
            TranslationLog::tid('Test with {parameter}') => 'Test ohne parameter'
        ]));

        $this->assertTrue($form->save());
        $this->assertNotEmpty($form->errors[TranslationLog::tid('Test with {parameter}')]);
        $this->assertEmpty($form->messageFile->getTranslation('de', 'Test with {parameter}'));
    }

    public function testMissingParameter2()
    {
        $this->becomeUser('Admin');
        $form = new TranslationForm();
        $this->assertTrue($form->load([
            'moduleId' => 'translation',
            'language' => 'de',
            'file' => 'test',
            TranslationLog::tid('Maximum of {n,plural,=1{# space} other{# spaces}}') => 'Maximal how to do this?'
        ]));

        $this->assertTrue($form->save());
        $this->assertNotEmpty($form->errors[TranslationLog::tid('Maximum of {n,plural,=1{# space} other{# spaces}}')]);
        $this->assertEmpty($form->messageFile->getTranslation('de', 'Maximum of {n,plural,=1{# space} other{# spaces}}'));
    }
}