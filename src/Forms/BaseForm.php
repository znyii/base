<?php

namespace ZnYii\Base\Forms;

use yii\base\Model;
use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnCore\Base\Libs\I18Next\Facades\I18Next;

abstract class BaseForm extends Model
{

    abstract public function i18NextConfig(): array;

    public function translateAttribute(string $attributeName): string
    {
        $config = $this->i18NextConfig();
        $attributeName = ArrayHelper::getValue($this->translateAliases(), $attributeName, $attributeName);
        return I18Next::t($config['bundle'], $config['file'] . '.attribute.' . $attributeName);
    }

    public function translateAliases(): array
    {
        return [];
    }

    public function attributeLabels()
    {
        $labels = [];
        foreach ($this->attributes() as $attributeName) {
            $labels[$attributeName] = $this->translateAttribute($attributeName);
        }
        return $labels;
    }
}
