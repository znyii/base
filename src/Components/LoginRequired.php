<?php

namespace ZnYii\Base\Components;

use Yii;
use yii\base\Component;
use yii\helpers\Url;

class LoginRequired extends Component
{

    public function init()
    {
        if (!Yii::$app->user->isGuest) {
            return;
        }
        $currentUri = $this->getCurrentUrl();
        $loginUrl = $this->getAuthUrl();
        if ($currentUri == $loginUrl) {
            return;
        }
        $this->redirect();
        parent::init();
    }

    private function getAuthUrl(): string
    {
        $uri = Url::to(Yii::$app->user->loginUrl);
        $uri = trim($uri, '/');
        return $uri;
    }

    private function getCurrentUrl(): string
    {
        $uri = Yii::$app->request->url;
        $uri = trim($uri, '/');
        return $uri;
    }

    private function redirect()
    {
        Yii::$app->response->redirect(Yii::$app->user->loginUrl);
        Yii::$app->response->send();
        exit;
    }
}
