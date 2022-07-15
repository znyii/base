<?php

namespace ZnYii\Base\Helpers;

use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use ZnCore\Arr\Helpers\ArrayHelper;
use ZnCore\Code\Helpers\PropertyHelper;
use ZnCore\Collection\Interfaces\Enumerable;
use ZnCore\Container\Helpers\ContainerHelper;
use ZnCore\Instance\Helpers\ClassHelper;
use ZnCore\Text\Helpers\Inflector;
use ZnDomain\Entity\Helpers\EntityHelper;
use ZnYii\Base\Base\DynamicForm;
use ZnYii\Base\Enums\ScenarionEnum;

class FormHelper
{

    /**
     * @param string $formClass
     * @return object | DynamicForm
     */
    public static function createFormByClass(string $formClass, string $scenario = ScenarionEnum::CREATE): object
    {
        $instance = ContainerHelper::getContainer()->get($formClass);
        if ($instance instanceof Model) {
            $model = $instance;
        } else {
            /** @var DynamicForm $model */
            $model = FormHelper::createModelByForm($instance);
            $model->setFormInstance($instance);
        }
        if ($model instanceof DynamicForm) {
            $form = $model->getFormInstance();
            /*if($form instanceof ScenarioInterface) {
                $form->setScenario($scenario);
            }*/
        }
        return $model;
    }

    public static function createModelByForm(object $form): DynamicForm
    {
        $model = new DynamicForm(EntityHelper::getAttributeNames($form));
        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post($model->formName());
            FormHelper::setAttributes($model, $postData);
            PropertyHelper::setAttributes($form, $model->toArray([], [], false));
        }
        if (method_exists($form, 'i18NextConfig')) {
            $model->setI18NextConfig($form->i18NextConfig());
        }
        return $model;
    }

    public static function setAttributes(Model $model, array $data)
    {
        $attributes = $model->attributes();
        foreach ($attributes as $attribute) {
            $uploadedFile = UploadedFile::getInstance($model, $attribute);
            if ($uploadedFile) {
//                $data[$attribute] = \ZnBundle\Storage\Domain\Helpers\UploadHelper::getSymfonyUploadedFileFromYii($uploadedFile);
                $data[$attribute] = new SymfonyUploadedFile($uploadedFile->tempName, $uploadedFile->name, $uploadedFile->type, $uploadedFile->error);
            }
        }
        $data = ArrayHelper::filter($data, $attributes);
        ClassHelper::configure($model, $data);
    }

    public static function extractAttributesForEntity(Model $model, string $entityClass): array
    {
        $data = $model->toArray();
        $attributes = EntityHelper::getAttributeNames($entityClass);
        $attributesUnderscore = array_map([Inflector::class, 'underscore'], $attributes);
        $attributes = array_merge($attributes, $attributesUnderscore);
        $data = ArrayHelper::filter($data, $attributes);
        $data = self::nullingEmptyItems($data);
        return $data;
    }

    public static function nullingEmptyItems(array $data): array
    {
        foreach ($data as $key => $value) {
            if (empty($value) && $value !== false && $value != 0) {
                $data[$key] = null;
            }
        }
        return $data;
    }

    public static function setErrorsToModel(Model $model, Enumerable $errorCollection): array
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