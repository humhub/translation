<?php

namespace app\modules\translation\assets;

class MainAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@app/modules/translation/assets';
    public $js = [
        'js/translation.js',
        'js/jquery.showLoading.js'
    ];
    public $css = [
        'css/translation.css',
        'css/showLoading.css'
    ];
}