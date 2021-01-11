<?php

namespace ZnYii\Base\Web\Actions;

use Yii;
use yii\web\BadRequestHttpException;
use ZnCore\Domain\Exceptions\UnprocessibleEntityException;
use ZnCore\Domain\Helpers\EntityHelper;
use ZnCore\Domain\Helpers\QueryHelper;
use ZnCore\Domain\Helpers\ValidationHelper;

class IndexAction extends BaseAction
{

    private $with = [];
    private $filterModel;
    private $defaultPerPage = 10;

    public function setFilterModel(?string $filterModel): void
    {
        $this->filterModel = $filterModel;
    }

    public function setWith(array $with)
    {
        $this->with = $with;
    }

    public function setDefaultPerPage(int $defaultPerPage): void
    {
        $this->defaultPerPage = $defaultPerPage;
    }

    public function run()
    {
        $query = QueryHelper::getAllParams(Yii::$app->request->get());
        if(Yii::$app->request->get('per-page') == null) {
            $query->perPage($this->defaultPerPage);
        }
        $query->with($this->with);
        $dataProvider = $this->service->getDataProvider($query);
        if ($this->filterModel) {
            $filterAttributes = QueryHelper::getFilterParams($query);
            $filterModel = EntityHelper::createEntity($this->filterModel, $filterAttributes);
            try {
                ValidationHelper::validateEntity($filterModel);
            } catch (UnprocessibleEntityException $e) {
                $errorCollection = $e->getErrorCollection();
                $errors = [];
                foreach ($errorCollection as $errorEntity) {
                    $errors[] = $errorEntity->getField() . ': ' . $errorEntity->getMessage();
                }
                throw new BadRequestHttpException(implode('<br/>', $errors));
            }
            $dataProvider->setFilterModel($filterModel);
        } else {
            $filterModel = null;
        }
        $this->runCallback([$dataProvider]);
        return $this->render('index', [
            'request' => Yii::$app->request,
            'dataProvider' => $dataProvider,
            'filterModel' => $filterModel,
            'queryParams' => Yii::$app->request->get(),
        ]);
    }
}
