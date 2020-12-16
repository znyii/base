<?php

namespace ZnYii\Base\Web\Assets;

use yii\web\AssetBundle;

abstract class BaseAsset extends AssetBundle
{

    public function init()
    {
        parent::init();
        if(YII_DEBUG && $_ENV['YII_ASSET_FORCE_COPY']) {
            $this->publishOptions['forceCopy'] = true;
        }
    }
}
