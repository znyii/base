<?php

namespace ZnYii\Base\Web\Widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;
use ZnCore\Base\Libs\I18Next\Facades\I18Next;

class CancelButtonWidget extends Widget
{

    public $buttonOptions = ['class' => 'btn btn-default'];

    public function run()
    {
        $html = Html::a(I18Next::t('core', 'action.cancel'), $this->getUrl(), $this->buttonOptions);
        return $html;
    }

    private function getUrl(): string
    {
        $action = Yii::$app->requestedAction;
        $route = $action->controller->module->id . '/' . $action->controller->id;
        $urlArray = ['/' . $route];
        $urlArray = array_merge($urlArray, Yii::$app->request->queryParams);
        return Url::toRoute($urlArray);
    }
}
