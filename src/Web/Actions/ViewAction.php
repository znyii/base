<?php

namespace ZnYii\Base\Web\Actions;

use yii\helpers\Url;
use Yii;
use ZnCore\Domain\Interfaces\Entity\EntityIdInterface;
use ZnCore\Domain\Libs\Query;

class ViewAction extends BaseAction
{

    private $with = [];

    public function setWith(array $with)
    {
        $this->with = $with;
    }

    public function run(int $id)
    {
        $query = new Query();
        $query->with($this->with);
        /** @var EntityIdInterface $entity */
        $entity = $this->service->oneById($id, $query);
        $this->runCallback([$entity]);
        return $this->render('view', [
            'request' => Yii::$app->request,
            'entity' => $entity,
        ]);
    }
}
