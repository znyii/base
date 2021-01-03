<?php

namespace ZnYii\Base\Web\Actions;

use Illuminate\Container\Container;
use ZnCore\Base\Legacy\Yii\Helpers\Inflector;
use ZnCore\Domain\Exceptions\UnprocessibleEntityException;
use ZnCore\Domain\Helpers\EntityHelper;
use ZnCore\Domain\Interfaces\Entity\EntityIdInterface;
use ZnCore\Domain\Libs\Query;
use ZnLib\Web\Yii2\Widgets\Toastr\widgets\Alert;
use Yii;
use ZnYii\Base\Forms\BaseForm;
use ZnYii\Base\Helpers\FormHelper;
use ZnYii\Base\Helpers\UnprocessibleErrorHelper;

class UpdateAction extends BaseFormAction
{

    public function run(int $id)
    {
        $entity = $this->readOne($id);
//        $this->runCallback();
        /** @var BaseForm $model */
        $model = Container::getInstance()->get($this->formClass);
        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post($model->formName());
            FormHelper::setAttributes($model, $postData);
            try {
                $this->service->updateById($id, FormHelper::extractAttributesForEntity($model, $this->entityClass));
                Alert::create($this->getSuccessMessage(), Alert::TYPE_SUCCESS);
                return $this->redirect($this->successRedirectUrl);
            } catch (UnprocessibleEntityException $e) {
                $errors = FormHelper::setErrorsToModel($model, $e->getErrorCollection());
                $errorMessage = implode('<br/>', $errors);
                Alert::create($errorMessage, Alert::TYPE_WARNING);
            } catch (\DomainException $e) {
                Alert::create($e->getMessage(), Alert::TYPE_WARNING);
            }
        } else {
            $data = EntityHelper::toArrayForTablize($entity);
            FormHelper::setAttributes($model, $data);
        }
        return $this->render('update', [
            'request' => Yii::$app->request,
            'model' => $model,
        ]);
    }

    private function readOne(int $id): EntityIdInterface
    {
        $query = new Query();
        $query->with($this->with);
        /** @var EntityIdInterface $entity */
        $entity = $this->service->oneById($id, $query);
        $this->runCallback([$entity]);
        return $entity;
    }
}
