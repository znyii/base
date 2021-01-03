<?php

namespace ZnYii\Base\Web\Actions;

use yii\helpers\Url;
use Yii;
use ZnCore\Domain\Interfaces\Entity\EntityIdInterface;
use ZnCore\Domain\Libs\Query;
use ZnLib\Web\Yii2\Widgets\Toastr\widgets\Alert;

class RestoreAction extends BaseAction
{

    private $with = [];
    private $successMessage;
    private $successMessageKey = 'restore_success';
    private $successRedirectUrl;

    public function setWith(array $with)
    {
        $this->with = $with;
    }

    public function setSuccessMessage(array $successMessage): void
    {
        $this->successMessage = $successMessage;
    }

    public function getSuccessMessage(): array
    {
        return $this->successMessage ?: $this->getI18NextParams($this->successMessageKey);
    }

    public function setSuccessRedirectUrl($successRedirectUrl): void
    {
        $this->successRedirectUrl = $successRedirectUrl;
    }

    public function run(int $id)
    {
        try {
            $this->service->restoreById($id);
            Alert::create($this->getSuccessMessage(), Alert::TYPE_SUCCESS);
        } catch (\DomainException $e) {
            Alert::create($e->getMessage(), Alert::TYPE_WARNING);
        }
        return $this->redirect($this->successRedirectUrl);
    }
}
