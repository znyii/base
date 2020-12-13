<?php

namespace ZnYii\Base\Web\Actions;

use ZnCore\Base\Legacy\Yii\Helpers\Inflector;
use ZnCore\Domain\Exceptions\UnprocessibleEntityException;
use ZnCore\Domain\Helpers\EntityHelper;
use ZnCore\Domain\Interfaces\Entity\EntityIdInterface;
use ZnCore\Domain\Libs\Query;
use ZnLib\Web\Yii2\Widgets\Toastr\widgets\Alert;
use Yii;

class UpdateAction extends BaseFormAction
{

    public function run(int $id)
    {
        $entity = $this->readOne($id);

        $data = EntityHelper::toArrayForTablize($entity);


//        $this->runCallback();
        $model = $this->createForm($data);
        if (Yii::$app->request->isPost) {
            try {
                $this->service->updateById($id, $this->extractAttributesForEntity($model->toArray()));
                Alert::create($this->getSuccessMessage(), Alert::TYPE_SUCCESS);
                return $this->redirect($this->successRedirectUrl);
            } catch (UnprocessibleEntityException $e) {
                $errors = $this->setErrorsToModel($model, $e->getErrorCollection());
                $errorMessage = implode('<br/>', $errors);
                Alert::create($errorMessage, Alert::TYPE_WARNING);
            }
        }
        return $this->render('update', [
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
