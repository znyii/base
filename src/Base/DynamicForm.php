<?php

namespace ZnYii\Base\Base;

use yii\base\DynamicModel;
use ZnCore\Base\Libs\Arr\Helpers\ArrayHelper;
use ZnCore\Base\Libs\I18Next\Facades\I18Next;

class DynamicForm extends DynamicModel
{

    private $_formName = 'entity';
    private $_formInstance;
    private $_i18NextConfig;

    public function formName()
    {
        return $this->_formName;
    }

    public function setFormName(string $name)
    {
        $this->_formName = $name;
    }

    public function getFormInstance(): ?object
    {
        return $this->_formInstance;
    }

    public function setFormInstance(object $formInstance): void
    {
        $this->_formInstance = $formInstance;
    }

    public function i18NextConfig(): array
    {
        return $this->_i18NextConfig;
    }

    public function setI18NextConfig(array $config): void
    {
        $this->_i18NextConfig = $config;
    }

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
