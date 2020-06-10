<?php

namespace humhub\modules\translation\tests\codeception\unit;

use humhub\modules\translation\models\forms\TranslationForm;
use humhub\modules\translation\models\parser\MessageParser;
use humhub\modules\translation\models\TranslationLog;
use tests\codeception\_support\HumHubDbTestCase;
use translation\TranslationTest;
use Yii;

class MessageParserTest extends HumHubDbTestCase
{
    public function testUnnamedParameter()
    {
        $result = MessageParser::parse('Price: {0}, Count: {1}, Subtotal: {2}');
        $this->assertCount(3, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_DEFAULT, $result['0']);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_DEFAULT, $result['1']);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_DEFAULT, $result['2']);
    }

    public function testCurrencyParameter()
    {
        $result = MessageParser::parse('Price: {0,number,currency}');
        $this->assertCount(1, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_NUMBER, $result['0']);
    }

    public function testCurrencyParameterNamed()
    {
        $result = MessageParser::parse('Price: {price,number,currency}');
        $this->assertCount(1, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_NUMBER, $result['price']);
    }

    public function testNumberParameter()
    {
        $result = MessageParser::parse('Balance: {0,number}');
        $this->assertCount(1, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_NUMBER, $result['0']);
    }

    public function testNumberParameterSpecialFormat()
    {
        $result = MessageParser::parse('Balance: {0,number,,000,000000}');
        $this->assertCount(1, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_NUMBER, $result['0']);
    }

    public function testNumberParameterNamed()
    {
        $result = MessageParser::parse('Balance: {balance,number}');
        $this->assertCount(1, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_NUMBER, $result['balance']);
    }

    public function testDateParameter()
    {
        $result = MessageParser::parse('Today is {0,date}');
        $this->assertCount(1, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_DATE, $result['0']);
    }

    public function testDateParameterWithFormat()
    {
        $result = MessageParser::parse('Today is {0,date,short}');
        $this->assertCount(1, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_DATE, $result['0']);
    }

    public function testDateParameterSpecialFormat()
    {
        $result = MessageParser::parse('Today is {0,date,yyyy-MM-dd}');
        $this->assertCount(1, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_DATE, $result['0']);
    }

    public function testTimeParameterNamed()
    {
        $result = MessageParser::parse('It is {t,time}');
        $this->assertCount(1, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_TIME, $result['t']);
    }

    public function testTimeParameterWithFormat()
    {
        $result = MessageParser::parse('It is {t,time,short}');
        $this->assertCount(1, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_TIME, $result['t']);
    }

    public function testTimeParameterWithFormat2()
    {
        $result = MessageParser::parse('It is {t,time,HH:mm}');
        $this->assertCount(1, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_TIME, $result['t']);
    }

    public function testSpellout()
    {
        $result = MessageParser::parse('This number is spelled as {n,spellout}');
        $this->assertCount(1, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_NUMBER, $result['n']);
    }

    public function testSpelloutComplex()
    {
        $result = MessageParser::parse('I am {n,spellout,%spellout-ordinal} agent');
        $this->assertCount(1, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_NUMBER, $result['n']);
    }

    public function testOrdinal()
    {
        $result = MessageParser::parse('You are the {n,ordinal} visitor here!');
        $this->assertCount(1, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_NUMBER, $result['n']);
    }

    public function testOrdinalComplex()
    {
        $result = MessageParser::parse('{n,ordinal,%digits-ordinal-feminine}');
        $this->assertCount(1, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_NUMBER, $result['n']);
    }

    public function testDuration()
    {
        $result = MessageParser::parse('You are here for {n,duration} already!');
        $this->assertCount(1, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_NUMBER, $result['n']);
    }

    public function testDurationComplex()
    {
        $result = MessageParser::parse('{n,duration,%in-numerals}');
        $this->assertCount(1, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_NUMBER, $result['n']);
    }

    public function testPlural()
    {
        $result = MessageParser::parse('There {n,plural,=0{are no cats} =1{is one cat} other{are # cats}}!');
        $this->assertCount(1, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_NUMBER, $result['n']);
    }

    public function testPluralWithOffset()
    {
        $result = MessageParser::parse('You {likeCount,plural,
            offset: 1
            =0{did not like this}
            =1{liked this}
            one{and one other person liked this}
            other{and # others liked this}
        }');
        $this->assertCount(1, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_NUMBER, $result['likeCount']);
    }

    public function testOrdinalSelection()
    {
        $result = MessageParser::parse('You are the {n,selectordinal,one{#st} two{#nd} few{#rd} other{#th}} visitor');
        $this->assertCount(1, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_NUMBER, $result['n']);
    }

    public function testSelection()
    {
        // Selction will currently always be number, this is sufficient for parameter validation
        $result = MessageParser::parse('{name} is a {gender} and {gender,select,female{she} male{he} other{it}} loves Yii!');
        $this->assertCount(2, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_DEFAULT, $result['name']);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_NUMBER, $result['gender']);
    }

    public function testComplex()
    {
        $result = MessageParser::parse('Test {test} {n,plural,=0{are no cats} =1{is one cat} other{are # cats}} {test2} {x,selectordinal,one{#st} two{#nd} few{#rd} other{#th}}');
        $this->assertCount(4, $result);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_DEFAULT, $result['test']);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_DEFAULT, $result['test2']);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_NUMBER, $result['n']);
        $this->assertEquals(MessageParser::PARAMETER_TYPE_NUMBER, $result['x']);
    }

    public function testCompareParameterValid()
    {
        $result = MessageParser::compareParameter(
            ['a' => MessageParser::PARAMETER_TYPE_DEFAULT],
            ['a' => MessageParser::PARAMETER_TYPE_DEFAULT]);

        $this->assertTrue($result);
    }

    public function testCompareParameterInvalidType()
    {
        $result = MessageParser::compareParameter(
            ['a' => MessageParser::PARAMETER_TYPE_DEFAULT],
            ['a' => MessageParser::PARAMETER_TYPE_TIME]);

        $this->assertNotTrue($result);
        $this->assertEquals(['a', -1], $result);
    }

    public function testCompareParameterMissingParameter()
    {
        $result =  MessageParser::compareParameter(
            ['a' => MessageParser::PARAMETER_TYPE_DEFAULT],
            []);

        $this->assertNotTrue($result);
        $this->assertEquals(['a', -1], $result);
    }

    public function testCompareParameterInvalidParameter()
    {
        $result =  MessageParser::compareParameter(
            ['a' => MessageParser::PARAMETER_TYPE_DEFAULT],
            ['b' => MessageParser::PARAMETER_TYPE_DEFAULT]);

        $this->assertNotTrue($result);
        $this->assertEquals(['a', -1], $result);
    }

    public function testCompareParameterInvalidAdditionalParameter()
    {
        $result =  MessageParser::compareParameter(
            [],
            ['b' => MessageParser::PARAMETER_TYPE_DEFAULT]);

        $this->assertNotTrue($result);
        $this->assertEquals(['b', 1], $result);
    }

    public function testCompareParameterInvalidAdditionalParameterMultiple()
    {
        $result =  MessageParser::compareParameter(
            ['b' => MessageParser::PARAMETER_TYPE_DEFAULT],
            ['b' => MessageParser::PARAMETER_TYPE_DEFAULT, 'c' => MessageParser::PARAMETER_TYPE_DEFAULT]);

        $this->assertNotTrue($result);
        $this->assertEquals(['c', 1], $result);
    }

    public function testGetNumberDummyData()
    {
        $this->assertInternalType('int', MessageParser::getDummyData(['number' => MessageParser::PARAMETER_TYPE_NUMBER])['number']);
    }

    public function testGetDateDummyData()
    {
        $this->assertInternalType('int', MessageParser::getDummyData(['date' => MessageParser::PARAMETER_TYPE_DATE])['date']);
    }

    public function testGetTimeDummyData()
    {
        $this->assertInternalType('int', MessageParser::getDummyData(['time' => MessageParser::PARAMETER_TYPE_TIME])['time']);
    }
}