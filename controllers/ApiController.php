<?php

namespace humhub\modules\translation\controllers;

use humhub\components\Controller;
use humhub\modules\translation\models\Languages;
use humhub\modules\translation\models\TranslationCoverage;
use Yii;

class ApiController extends Controller
{
    /**
     * Inits the Translate Controller
     *
     */
    public function actionIndex()
    {
        Yii::$app->response->format = 'json';
        header('Access-Control-Allow-Origin: *');


        $res = Yii::$app->cache->get("translation_status");
        if (!$res) {
            $res = [];
            foreach (Languages::getAllTranslatableLanguages() as $lang) {
                $res[] = [$lang, TranslationCoverage::getLanguageCoverage($lang)];
            }
            Yii::$app->cache->set("translation_status", $res);
        }


        return $this->asJson($res);
    }

}
