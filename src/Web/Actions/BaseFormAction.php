<?php

namespace ZnYii\Base\Web\Actions;

use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use yii\base\Model;
use ZnCore\Base\Helpers\ClassHelper;
use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnCore\Base\Legacy\Yii\Helpers\Inflector;
use ZnCore\Domain\Helpers\EntityHelper;
use ZnYii\Base\Forms\BaseForm;
use Yii;

abstract class BaseFormAction extends BaseAction
{

    protected $with = [];
    protected $sort = [];
    protected $formClass;
    protected $entityClass;
    protected $successMessage;
    protected $successMessageKey = 'create_success';
    protected $successRedirectUrl;

    public function setWith(array $with)
    {
        $this->with = $with;
    }

    public function setSort(array $sort)
    {
        $this->sort = $sort;
    }

    public function setFormClass(string $formClass): void
    {
        $this->formClass = $formClass;
    }

    public function setEntityClass(string $entityClass): void
    {
        $this->entityClass = $entityClass;
    }

    public function setSuccessMessage(array $successMessage): void
    {
        $this->successMessage = $successMessage;
    }

    public function getSuccessMessage(): array
    {
        return $this->successMessage ?: $this->getI18NextParams($this->successMessageKey);
    }

    public function setSuccessRedirectUrl(array $successRedirectUrl): void
    {
        $this->successRedirectUrl = $successRedirectUrl;
    }

    protected function setErrorsToModel(Model $model, Collection $errorCollection): array
    {
        $errors = [];
        foreach ($errorCollection as $errorEntity) {
            $fieldSnackCase = Inflector::underscore($errorEntity->getField());
            $model->addError($fieldSnackCase, $errorEntity->getMessage());
            $errors[] = $model->translateAttribute($fieldSnackCase) . ':' . $errorEntity->getMessage();
        }
        return $errors;
    }

    protected function createForm(array $data = []): BaseForm
    {
        /** @var Model $model */
        $model = Container::getInstance()->get($this->formClass);
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post($model->formName());
        }
        $attributes = $model->attributes();
        $data = ArrayHelper::filter($data, $attributes);
        ClassHelper::configure($model, $data);
        return $model;
    }

    protected function extractAttributesForEntity(array $data): array
    {
        $entityClass = $this->entityClass;
        $attributes = EntityHelper::getAttributeNames(new $entityClass);
        $attributes = array_map([Inflector::class, 'underscore'], $attributes);
        $data = ArrayHelper::filter($data, $attributes);
        $data = ArrayHelper::removeEmptyItems($data);
        return $data;
    }
}