<?php

namespace ZnYii\Base\Web\Actions;

use Yii;
use yii\web\BadRequestHttpException;
use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnCore\Domain\Exceptions\UnprocessibleEntityException;
use ZnCore\Domain\Helpers\EntityHelper;
use ZnCore\Domain\Helpers\QueryHelper;
use ZnCore\Domain\Helpers\ValidationHelper;
use ZnCore\Domain\Libs\Query;

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

    private function removeEmptyParameters(array $filterAttributes): array {
        foreach ($filterAttributes as $attribute => $value) {
            if($value === '') {
                unset($filterAttributes[$attribute]);
            }
        }
        return $filterAttributes;
    }

    private function forgeFilterModel(): object {
        $filterAttributes = Yii::$app->request->get('filter');
//            $filterAttributes = QueryHelper::getFilterParams($query);
        $filterAttributes = $filterAttributes ? $this->removeEmptyParameters($filterAttributes) : [];
        $filterModel = EntityHelper::createEntity($this->filterModel, $filterAttributes);
//        dd($filterModel);
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
        return $filterModel;
    }

    private function forgeQueryFromRequest(): Query {
        $query = QueryHelper::getAllParams(Yii::$app->request->get());
        $query->removeParam(Query::WHERE);
        $query->removeParam(Query::WHERE_NEW);
        if(Yii::$app->request->get('per-page') == null) {
            $query->perPage($this->defaultPerPage);
        }
        return $query;
    }

    public function run()
    {
        $query = $this->forgeQueryFromRequest();
        $query->with($this->with);
        $dataProvider = $this->service->getDataProvider($query);
        if ($this->filterModel) {
            $filterModel = $this->forgeFilterModel();
            $dataProvider->setFilterModel($filterModel);
        }
        $this->runCallback([$dataProvider]);
        return $this->render('index', [
            'request' => Yii::$app->request,
            'dataProvider' => $dataProvider,
            'filterModel' => $dataProvider->getFilterModel(),
            'queryParams' => Yii::$app->request->get(),
        ]);
    }
}
