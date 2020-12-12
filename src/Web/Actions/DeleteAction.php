<?php

namespace ZnYii\Base\Web\Actions;

use yii\helpers\Url;
use Yii;
use ZnCore\Domain\Interfaces\Entity\EntityIdInterface;
use ZnCore\Domain\Libs\Query;
use ZnLib\Web\Yii2\Widgets\Toastr\widgets\Alert;

class DeleteAction extends BaseAction
{

    private $with = [];
    private $successMessage;
    private $successMessageKey = 'delete_success';
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
        $this->service->deleteById($id);
        Alert::create($this->getSuccessMessage(), Alert::TYPE_SUCCESS);
        return $this->redirect($this->successRedirectUrl);
    }
}
