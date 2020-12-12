<?php

namespace ZnYii\Base\Web\Actions;

use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use Packages\Shop\Yii2\Web\forms\OrderForm;
use yii\base\Model;
use ZnCore\Base\Helpers\ClassHelper;
use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnCore\Base\Legacy\Yii\Helpers\Inflector;
use ZnCore\Domain\Exceptions\UnprocessibleEntityException;
use ZnCore\Domain\Helpers\EntityHelper;
use ZnCore\Domain\Helpers\QueryHelper;
use Yii;
use ZnLib\Web\Yii2\Widgets\Toastr\widgets\Alert;
use ZnYii\Base\Forms\BaseForm;

class CreateAction extends BaseAction
{

    private $with = [];
    private $sort = [];
    private $formClass;
    private $entityClass;
    private $successMessage;
    private $successMessageKey = 'create_success';
    private $successRedirectUrl;

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

    public function run()
    {
        $this->runCallback();
        $model = $this->createForm();
        if (Yii::$app->request->isPost) {
            try {
                $this->service->create($this->extractAttributesForEntity($model->toArray()));
                Alert::create($this->getSuccessMessage(), Alert::TYPE_SUCCESS);
                return $this->redirect($this->successRedirectUrl);
            } catch (UnprocessibleEntityException $e) {
                $errors = $this->setErrorsToModel($model, $e->getErrorCollection());
                $errorMessage = implode('<br/>', $errors);
                Alert::create($errorMessage, Alert::TYPE_WARNING);
            }
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    private function setErrorsToModel(Model $model, Collection $errorCollection): array
    {
        $errors = [];
        foreach ($errorCollection as $errorEntity) {
            $fieldSnackCase = Inflector::underscore($errorEntity->getField());
            $model->addError($fieldSnackCase, $errorEntity->getMessage());
            $errors[] = $model->translateAttribute($fieldSnackCase) . ':' . $errorEntity->getMessage();
        }
        return $errors;
    }

    private function createForm(): BaseForm
    {
        $model = Container::getInstance()->get($this->formClass);
        if (Yii::$app->request->isPost) {
            $this->loadFormDataFromPost($model);
        }
        return $model;
    }

    private function loadFormDataFromPost(Model $model)
    {
        $data = Yii::$app->request->post($model->formName());
        ClassHelper::configure($model, $data);
    }

    private function extractAttributesForEntity(array $data): array
    {
        $entityClass = $this->entityClass;
        $attributes = EntityHelper::getAttributeNames(new $entityClass);
        $attributes = array_map([Inflector::class, 'underscore'], $attributes);
        $data = ArrayHelper::filter($data, $attributes);
        $data = ArrayHelper::removeEmptyItems($data);
        return $data;
    }
}
