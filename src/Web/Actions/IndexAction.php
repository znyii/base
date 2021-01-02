<?php

namespace ZnYii\Base\Web\Actions;

use ZnCore\Domain\Helpers\QueryHelper;
use Yii;
use ZnCore\Domain\Interfaces\Entity\ValidateEntityInterface;
use ZnCore\Domain\Interfaces\Service\ServiceDataProviderByFilterInterface;

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
        if($this->service instanceof ServiceDataProviderByFilterInterface) {
            $filterModel = new $this->filterModel;
            $dataProvider = $this->service->getDataProviderByFilter($query, [], $filterModel);
        } else {
            $dataProvider = $this->service->getDataProvider($query);
        }
        $this->runCallback([$dataProvider]);
        return $this->render('index', [
            'request' => Yii::$app->request,
            'dataProvider' => $dataProvider,
            'queryParams' => Yii::$app->request->get(),
        ]);
    }
}
