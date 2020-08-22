<?php

namespace humhub\modules\translation\widgets;

use humhub\modules\translation\helpers\Url;
use humhub\modules\translation\models\forms\TranslationForm;
use humhub\widgets\JsWidget;

class TranslationFormWidget extends JsWidget
{
    public $jsWidget = 'translation.Form';

    public $id = 'translation-editor-translations';

    public $init = true;

    /**
     * @var TranslationForm
     */
    public $model;

    public function run()
    {
        return $this->render('translationForm', [
            'options' => $this->getOptions(),
            'model' => $this->model
        ]);
    }

    public function getData()
    {
        return [
            'load-url' => Url::toReloadForm()
        ];
    }
}