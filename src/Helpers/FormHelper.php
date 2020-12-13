<?php

namespace ZnYii\Base\Helpers;

use Illuminate\Support\Collection;
use yii\base\Model;
use ZnCore\Base\Helpers\ClassHelper;
use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnCore\Base\Legacy\Yii\Helpers\Inflector;
use ZnCore\Domain\Helpers\EntityHelper;

class FormHelper
{

    public static function setAttributes(Model $model, array $data)
    {
        $attributes = $model->attributes();
        $data = ArrayHelper::filter($data, $attributes);
        ClassHelper::configure($model, $data);
    }

    public static function extractAttributesForEntity(Model $model, string $entityClass): array
    {
        $data = $model->toArray();
        $attributes = EntityHelper::getAttributeNames(new $entityClass);
        $attributes = array_map([Inflector::class, 'underscore'], $attributes);
        $data = ArrayHelper::filter($data, $attributes);
        $data = ArrayHelper::nullingEmptyItems($data);
        return $data;
    }

    public static function setErrorsToModel(Model $model, Collection $errorCollection): array
    {
        $errors = [];
        foreach ($errorCollection as $errorEntity) {
            $fieldSnackCase = Inflector::underscore($errorEntity->getField());
            $model->addError($fieldSnackCase, $errorEntity->getMessage());
            $errors[] = $model->getAttributeLabel($fieldSnackCase) . ':' . $errorEntity->getMessage();
        }
        return $errors;
    }
}