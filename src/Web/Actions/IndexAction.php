<?php

namespace ZnYii\Base\Web\Actions;

use ZnCore\Domain\Helpers\QueryHelper;
use Yii;

class IndexAction extends BaseAction
{

    private $with = [];
    private $sort = [];

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
        $this->runCallback([$dataProvider]);
        return $this->render('index', [
            'request' => Yii::$app->request,
            'dataProvider' => $dataProvider,
            'queryParams' => Yii::$app->request->get(),
        ]);
    }
}
