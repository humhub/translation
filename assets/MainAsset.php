<?php

namespace humhub\modules\translation\assets;

use Yii;
use humhub\components\assets\AssetBundle;
use humhub\modules\ui\view\components\View;

class MainAsset extends AssetBundle
{
    public $defer = true;
    public $forceCopy = false;
    public $sourcePath = '@translation/resources';
    public $js = [
        'js/humhub.translation.js',
        'js/jquery.showLoading.js',
    ];
    public $css = [
        'css/translation.css',
        'css/showLoading.css',
    ];

    /**
     * @param View $view
     * @return AssetBundle
     */
    public static function register($view)
    {
        $view->registerJsConfig('translation', [
            'text' => [
                'warn.unload' => Yii::t('TranslationModule.base', 'There are unsaved changes, do you really want to leave this page?'),
            ],
        ]);
        return parent::register($view);
    }
}
