<?php

namespace ZnYii\Base\Helpers;

use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use ZnCore\Base\Libs\Instance\Helpers\ClassHelper;
use ZnCore\Base\Libs\Arr\Helpers\ArrayHelper;
use ZnCore\Base\Libs\Text\Helpers\Inflector;
use ZnCore\Base\Libs\Container\Helpers\ContainerHelper;
use ZnCore\Domain\Entity\Helpers\EntityHelper;
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
            EntityHelper::setAttributes($form, $model->toArray([], [], false));
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