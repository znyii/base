<?php

namespace ZnYii\Base\Helpers;

use Yii;
use yii\bootstrap\BootstrapAsset;
use yii2rails\extension\store\Store;
use yii2rails\extension\web\helpers\Page;
use ZnCore\Base\Enums\Measure\TimeEnum;
use ZnCore\Base\Libs\Enum\Helpers\EnumHelper;
use ZnCore\Base\Libs\App\Helpers\EnvHelper;
use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnCore\Base\Legacy\Yii\Helpers\FileHelper;
use ZnCore\Base\Legacy\Yii\Helpers\Html;
use ZnCore\Base\Libs\FileSystem\Helpers\FileStorageHelper;

class Debug
{

    private static $isLogged = false;

    public static function getRuntime($unit = TimeEnum::SECOND_PER_SECOND, $precision = 2)
    {
        EnumHelper::validate(TimeEnum::class, $unit);
        $runtimeSecond = microtime(true) - MICRO_TIME;
        $runtime = $runtimeSecond / $unit;
        $runtime = round($runtime, $precision);
        return $runtime;
    }

    public static function log($val)
    {
        $url = Yii::$app->request->url;
        if (!empty($url) && strpos($url, '/debug/') !== false) {
            return null;
        }
        $file = Yii::getAlias('@runtime/logs/debug') . DIRECTORY_SEPARATOR . date('Y-m-d', TIMESTAMP) . '.log';
        if (file_exists($file)) {
            $log = FileStorageHelper::load($file);
        } else {
            $log = '';
        }
        if (is_object($val)) {
            $val = ArrayHelper::toArray($val);
        }
        $store = new Store('php');
        $content = $store->encode($val);
        if (self::$isLogged) {
            $log .= PHP_EOL . PHP_EOL . ' ------ ' . PHP_EOL . PHP_EOL;
        } else {
            $spliter = ' ' . str_repeat('=', 30) . ' ';
            $log .= PHP_EOL . PHP_EOL . $spliter . date('H-i-s', TIMESTAMP) . $spliter . PHP_EOL . PHP_EOL;
        }
        $log .= $content;
        FileStorageHelper::save($file, $log);
        self::$isLogged = true;
    }

    public static function prr($val, $exit = false, $forceToArray = false)
    {
        if (!empty($forceToArray) && !is_scalar($val)) {
            $val = ArrayHelper::toArray($val);
        }
        if (class_exists('Yii')) {
            self::varDump($val, $exit);
        } else {
            $content = '<pre style="font-size: 8pt;">' . print_r($val, 1) . '</pre>';
            echo $content;
            if ($exit) {
                exit;
            }
        }
    }

    public static function varDump($val, $exit = false)
    {
        if (APP == API) {
            if (is_object(Yii::$app)) {
                $response = Yii::$app->getResponse();
                $response->clearOutputBuffers();
                $response->setStatusCode(200);
                //$response->format = \yii\web\Response::FORMAT_JSON;
                $response->content = json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $response->send();
                Yii::$app->end();
            } else {
                echo json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
        if (!EnvHelper::isConsole()) {
            $val = Html::recursiveHtmlEntities($val);
        }
        $store = new Store('php');
        $content = $store->encode($val);
        if (!EnvHelper::isConsole() && APP != API) {
            $content = '<pre style="font-size: 8pt;">' . $content . '</pre>';
        }
        if ($exit) {
            self::showContent($content);
            exit;
        }
        echo $content;
    }

    private static function showContent($content)
    {
        if (EnvHelper::isConsole()) {
            echo $content;
            exit;
        }
        if (!empty(Yii::$app->view)) {
            BootstrapAsset::register(Yii::$app->view);
            Yii::$app->view->registerCss('body { margin: 20px; }');
            Page::beginDraw();
            echo $content;
            Page::endDraw();
        } else {
            echo $content;
        }
        exit;
    }

}
