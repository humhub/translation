<?php

namespace humhub\modules\translation\widgets;

use humhub\modules\translation\models\BasePath;
use humhub\modules\ui\form\widgets\BasePicker;

class TranslationFilePicker extends BasePicker
{
    public function init()
    {
        $this->defaultResults = BasePath::getModuleIds();
    }

    /**
     * Used to retrieve the option text of a given $item.
     *
     * @param \yii\db\ActiveRecord $item selected item
     * @return string item option text
     */
    protected function getItemText($item)
    {
        return $item;
    }

    protected function getItemKey($item)
    {
        return $item;
    }

    /**
     * Used to retrieve the option image url of a given $item.
     *
     * @param \yii\db\ActiveRecord $item selected item
     * @return string|null image url or null if no selection image required.
     */
    protected function getItemImage($item)
    {
        return null;
    }
}
