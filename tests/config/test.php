<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\modules\translation\tests\codeception\fixtures\TranslationLogFixture;

return [
    'modules' => ['translation'],
    'fixtures' => [
        'default',
        TranslationLogFixture::class,
    ],
];
