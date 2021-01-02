<?php

namespace ZnYii\Base\Web\Actions;

use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnCore\Domain\Helpers\EntityHelper;
use ZnCore\Domain\Helpers\QueryHelper;
use Yii;
use ZnCore\Domain\Interfaces\Entity\ValidateEntityInterface;
use ZnCore\Domain\Interfaces\Service\ServiceDataProviderByFilterInterface;
use ZnCore\Domain\Libs\Query;

class IndexAction extends BaseAction
{

    private $with = [];
    private $sort = [];
    private $filterModel;

    public function setFilterModel(?string $filterModel): void
    {
        $this->filterModel = $filterModel;
    }

    public function setWith(array $with)
    {
        $this->with = $with;
    }

    public function setSort(array $sort)
    {
        $this->sort = $sort;
    }

    public function run()
    {
        $query = QueryHelper::getAllParams(Yii::$app->request->get());
        $query->with($this->with);
        $query->addOrderBy($this->sort);
        $dataProvider = $this->service->getDataProvider($query);
        if ($this->filterModel) {
            $filterAttributes = QueryHelper::getFilterParams($query);
            $filterModel = EntityHelper::createEntity($this->filterModel, $filterAttributes);
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
