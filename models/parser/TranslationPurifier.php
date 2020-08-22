<?php


namespace humhub\modules\translation\models\parser;


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
        $def->addAttribute('img', 'src', new ParameterURIDef());
    }
}