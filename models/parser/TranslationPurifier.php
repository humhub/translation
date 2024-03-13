<?php


namespace humhub\modules\translation\models\parser;


use HTMLPurifier_AttrDef_Enum;
use yii\helpers\HtmlPurifier;

class TranslationPurifier extends HtmlPurifier
{
    /**
     * @inheritDoc
     */
    public static function configure( $config)
    {
        // https://stackoverflow.com/questions/4566301/htmlpurifier-with-an-html5-doctype
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $def =  $config->getHTMLDefinition(true);
        $def->addAttribute('a', 'href', new ParameterURIDef());
        $def->addAttribute('a', 'target', new HTMLPurifier_AttrDef_Enum(['_blank', '_self', '_parent', '_top']));
        $def->addAttribute('img', 'src', new ParameterURIDef());
    }
}