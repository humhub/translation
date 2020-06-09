<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\translation\tests\codeception\fixtures;

use humhub\modules\translation\models\TranslationLog;
use yii\test\ActiveFixture;

class TranslationLogFixture extends ActiveFixture
{
    public $modelClass = TranslationLog::class;
    public $dataFile = '@translation/tests/codeception/fixtures/data/translationLog.php';
}
