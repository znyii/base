<?php

namespace ZnYii\Base\Web\Actions;

use yii\base\Action;
use Yii;
use ZnCore\Domain\Interfaces\Service\CrudServiceInterface;

abstract class BaseAction extends Action
{

    /** @var CrudServiceInterface */
    protected $service;
    protected $callback;
    protected $i18NextConfig;

    public function setService(CrudServiceInterface $service): void
    {
        $this->service = $service;
    }

    public function setCallback(callable $callback): void
    {
        $this->callback = $callback;
    }

    public function runCallback(array $params = []): void
    {
        if ($this->callback) {
            call_user_func_array($this->callback, $params);
        }
    }

    public function setI18NextConfig(array $i18NextConfig): void
    {
        $this->i18NextConfig = $i18NextConfig;
    }

    protected function getI18NextParams(string $key): array
    {
        $bundle = $this->i18NextConfig['bundle'];
        $file = $this->i18NextConfig['file'];
        return [$bundle, $file . '.' . $key];
    }

    protected function render($view, $params = [])
    {
        return $this->controller->render($view, $params);
    }

    protected function redirect($url, $statusCode = 302) {
        return $this->controller->redirect($url, $statusCode = 302);
    }
}
