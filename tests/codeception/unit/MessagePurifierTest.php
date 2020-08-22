<?php

namespace humhub\modules\translation\tests\codeception\unit;

use humhub\modules\translation\models\parser\TranslationPurifier;
use tests\codeception\_support\HumHubDbTestCase;

class MessagePurifierTest extends HumHubDbTestCase
{
    public function testParameterInLinkIsNotEscaped()
    {
        $this->assertEquals('<a href="{asdf}">{asdf}</a>', TranslationPurifier::process('<a href="{asdf}">{asdf}</a>'));
    }

    public function testOnlySingleParameterInLinkNotEscaped1()
    {
        $this->assertEquals('<a href="%7Basdf%7Dnonparam">{asdf}</a>', TranslationPurifier::process('<a href="{asdf}nonparam">{asdf}</a>'));
    }

    public function testOnlySingleParameterInLinkNotEscaped2()
    {
        $this->assertEquals('<a href="nonparam%7Basdf%7D">{asdf}</a>', TranslationPurifier::process('<a href="nonparam{asdf}">{asdf}</a>'));
    }

    public function testParameterInImageNotEscaped()
    {
        $this->assertEquals('<img src="{asdf}" alt="Test">', TranslationPurifier::process('<img src="{asdf}"  alt="Test">'));
    }

    public function testOnlySingleParameterInImageNotEscaped()
    {
        $this->assertEquals('<img src="%7Basdf%7Dnonparam" alt="Test">', TranslationPurifier::process('<img src="{asdf}nonparam"  alt="Test">'));
    }

    public function testOnlySingleParameterInImageNotEscaped2()
    {
        $this->assertEquals('<img src="nonparam%7Basdf%7D" alt="Test">', TranslationPurifier::process('<img src="nonparam{asdf}"  alt="Test">'));
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