<?php

namespace humhub\modules\translation\models\validators;

use yii\validators\Validator;

class MessageFileNameValidator extends Validator
{
    public const FILE_FILTER = [
        'Browser.php',
        'yii.php',
        'zii.php',
        'ui.php',
    ];

    public function validateAttribute($model, $attribute)
    {
        $fileName = $model->$attribute;

        if (in_array($fileName, static::FILE_FILTER)) {
            $model->addError($attribute, 'Invalid file name');
        }

        if (!preg_match('/\.php$/', (string) $fileName)) {
            $model->addError($attribute, 'Invalid file name');
        }
    }

}
