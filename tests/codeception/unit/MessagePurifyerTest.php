<?php

namespace humhub\modules\translation\tests\codeception\unit;

use humhub\modules\translation\models\parser\TranslationPurifier;
use tests\codeception\_support\HumHubDbTestCase;

class MessagePurifyerTest extends HumHubDbTestCase
{
    public function testParameterInLinkIsNotEscaped()
    {
        $this->assertEquals('<a href="{asdf}">{asdf}</a>', TranslationPurifier::process('<a href="{asdf}">{asdf}</a>'));
    }

    public function testParameterInImageNotEscaped()
    {
        $this->assertEquals('<img src="{asdf}" alt="Test">', TranslationPurifier::process('<img src="{asdf}"  alt="Test">'));
    }

    public function testNonXhtmlTranslation()
    {
        $this->assertEquals('<br>', TranslationPurifier::process('<br>'));
    }

    /*public function testNonEntityTranslation()
    {
        $this->assertEquals('&', TranslationPurifier::process('&'));
    }*/

}