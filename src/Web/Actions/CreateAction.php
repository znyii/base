<?php

namespace ZnYii\Base\Web\Actions;

use Illuminate\Container\Container;
use ZnCore\Domain\Exceptions\UnprocessibleEntityException;
use ZnLib\Web\Yii2\Widgets\Toastr\widgets\Alert;
use Yii;
use ZnYii\Base\Forms\BaseForm;
use ZnYii\Base\Helpers\FormHelper;
use ZnYii\Base\Helpers\UnprocessibleErrorHelper;

class CreateAction extends BaseFormAction
{

    public function run()
    {
        $this->runCallback();
        /** @var BaseForm $model */
        $model = Container::getInstance()->get($this->formClass);
        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post($model->formName());
            FormHelper::setAttributes($model, $postData);
            try {
                $this->service->create(FormHelper::extractAttributesForEntity($model, $this->entityClass));
                Alert::create($this->getSuccessMessage(), Alert::TYPE_SUCCESS);
                return $this->redirect($this->successRedirectUrl);
            } catch (UnprocessibleEntityException $e) {
                $errors = FormHelper::setErrorsToModel($model, $e->getErrorCollection());
                $errorMessage = implode('<br/>', $errors);
                Alert::create($errorMessage, Alert::TYPE_WARNING);
            }
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }
}
