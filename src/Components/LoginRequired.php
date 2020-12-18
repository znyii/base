<?php

namespace ZnYii\Base\Components;

use Yii;
use yii\base\Component;
use yii\helpers\Url;

class LoginRequired extends Component
{

    public function init()
    {
        if ( ! Yii::$app->user->isGuest) {
            return;
        }
        $currentUri = $this->getCurrentUrl();
        $loginUrl = Yii::$app->user->loginUrl[0];
        if ($currentUri == $loginUrl) {
            return;
        }
        $this->redirect();
        /*foreach ($this->ignoreUrls as $url) {
            if (strpos($currentUri, $url) !== 0) {
                $this->redirect();
            }
        }*/
        parent::init();
    }

    private function getCurrentUrl(): string
    {
        $uri = str_replace(Url::base(), '', Yii::$app->request->url);
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
