<?php

namespace humhub\modules\translation\controllers;

use Yii;

class ApiController extends \humhub\components\Controller
{

    /**
     * Inits the Translate Controller
     * 
     * @param type $action
     * @return type
     */
    public function actionIndex()
    {
        Yii::$app->response->format = 'json';
        header('Access-Control-Allow-Origin: *');


        $res = Yii::$app->cache->get("translation_status");
        if ($res === false) {
            $res = array();
            foreach ($this->module->getLanguages() as $lang) {
                $res[] = array($lang, $this->module->getLanguagePercentage($lang));
            }
            Yii::$app->cache->set("translation_status", $res);
        }


        return $res;
    }

}
