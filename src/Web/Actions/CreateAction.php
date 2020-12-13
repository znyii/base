<?php

namespace ZnYii\Base\Web\Actions;

use ZnCore\Domain\Exceptions\UnprocessibleEntityException;
use ZnLib\Web\Yii2\Widgets\Toastr\widgets\Alert;
use Yii;

class CreateAction extends BaseFormAction
{

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
}
