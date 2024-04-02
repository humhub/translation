<?php

namespace humhub\modules\translation\models\parser;

use yii\helpers\HtmlPurifier;

class TranslationPurifier extends HtmlPurifier
{
    /**
     * @inheritDoc
     */
    public static function configure($config)
    {
        // Set HTMLPurifier configuration
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('Attr.EnableID', true);

        // Allow specific tags and attributes
        $config->set('HTML.Allowed', 'p,b,i,u,s,a[href|target],img[src|alt],ul,ol,li,blockquote,code,pre,span,hr,br,strong');

        // Allow non-ASCII characters
        $config->set('Core.EscapeNonASCIICharacters', false);

        // To avoid escaping inside the attributes
        $def = $config->getHTMLDefinition(true);
        $def->addAttribute('a', 'href', new ParameterURIDef());
        $def->addAttribute('img', 'src', new ParameterURIDef());
    }
}
