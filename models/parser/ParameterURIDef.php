<?php

namespace humhub\modules\translation\models\parser;

class ParameterURIDef extends \HTMLPurifier_AttrDef_URI
{
    public function validate($uri, $config, $context)
    {
        if(preg_match('/^\{[a-zA-Z0-9_]+\}$/', $uri)) {
            return true;
        }

        return parent::validate($uri, $config, $context);
    }
}