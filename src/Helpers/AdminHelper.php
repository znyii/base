<?php

namespace ZnYii\Base\Helpers;

class AdminHelper
{

    public static function defineModule($class) {
        return $class;
        return [
            'class' => $class,
            'as access' => [
                'class' => 'yii\filters\AccessControl',
                'rules' => [
                    [
                        'allow' => false,
                        'roles' => ['?'],
                    ],
                ],
            ],
        ];
    }
}