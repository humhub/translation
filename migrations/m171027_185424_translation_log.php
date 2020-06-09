<?php

use humhub\components\Migration;

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */


class m171027_185424_translation_log extends Migration
{
    public function safeUp()
    {
        $this->createTable('translation_log', [
            'id' => $this->primaryKey(),
            'language' => $this->string(10),
            'module_id' => $this->string(),
            'file' => $this->string(),
            'message' => $this->text(),
            'translation_old' => $this->text(),
            'translation' => $this->text(),
        ]);
    }

    public function safeDown()
    {
        echo "m171027_185423_sequence.\n";

        return false;
    }
}
