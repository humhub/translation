<?php

namespace humhub\modules\translation\stream\filters;

use humhub\modules\stream\models\filters\StreamQueryFilter;

class ModuleIdFilter extends StreamQueryFilter
{
    public const ID = 'moduleId';

    public const CATEGORY = 'moduleIds';

    public $moduleIds = [];

    public function rules()
    {
        return [
            ['moduleIds', 'safe'],
        ];
    }

    public function apply()
    {
        if (empty($this->moduleIds)) {
            return;
        }

        $this->query->andWhere(['in', 'translation_log.module_id', $this->moduleIds]);
    }
}
