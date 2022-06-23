<?php

namespace ZnYii\Base\Helpers;

use yii\helpers\Url;
use ZnBundle\User\Domain\Interfaces\Entities\IdentityEntityInterface;
use ZnCore\Base\Libs\Status\Enums\StatusEnum;
use ZnCore\Base\Libs\Arr\Helpers\ArrayHelper;
use ZnLib\Web\Helpers\Html;
use ZnCore\Base\Libs\I18Next\Facades\I18Next;
use ZnCore\Domain\Interfaces\Entity\EntityIdInterface;

/**
 * Class ActionHelper
 * @package ZnYii\Base\Helpers
 * @deprecated 
 * @see \ZnLib\Web\Symfony4\MicroApp\Helpers\ActionHelper
 */
class ActionHelper
{

    const TYPE_LINK = 'link';
    const TYPE_BUTTON = 'button';

    public static function getUpdateActionOptions(EntityIdInterface $entity, string $baseUrl) {
        $options['href'] = Url::to([$baseUrl . '/update', 'id' => $entity->getId()]);
        $options['type'] = 'primary';
        $options['title'] = I18Next::t('core', 'action.update');
        $options['icon'] = 'fa fa fa-edit';
        return $options;
    }

    public static function getRestoreActionOptions(EntityIdInterface $entity, string $baseUrl) {
        $options['confirm'] = I18Next::t('web', 'message.restore_confirm');
        $options['type'] = 'success';
        $options['href'] = Url::to([$baseUrl . '/restore', 'id' => $entity->getId()]);
        $options['title'] = I18Next::t('core', 'action.restore');
        $options['icon'] = 'fas fa-trash-restore';
        return $options;
    }

    public static function getDeleteActionOptions(EntityIdInterface $entity, string $baseUrl) {
        $options['confirm'] = I18Next::t('web', 'message.delete_confirm');
        $options['type'] = 'danger';
        $options['href'] = Url::to([$baseUrl . '/delete', 'id' => $entity->getId()]);
        $options['title'] = I18Next::t('core', 'action.delete');
        $options['icon'] = 'fa fa-trash';
        return $options;
    }

    public static function generateAction(EntityIdInterface $entity, string $baseUrl, string $action, string $type) {
        $methodName = "get{$action}ActionOptions";
        $options = call_user_func_array([self::class, $methodName], [$entity, $baseUrl]);
        return self::generate($options, $type);
    }

    public static function generateRestoreOrDeleteAction(EntityIdInterface $entity, string $baseUrl, string $type, array $extraOptions = []) {
        if($entity->getStatusId() === StatusEnum::DELETED) {
            return self::generateRestoreAction($entity, $baseUrl, $type, $extraOptions);
        } else {
            return self::generateDeleteAction($entity, $baseUrl, $type, $extraOptions);
        }
    }

    public static function generateUpdateAction(EntityIdInterface $entity, string $baseUrl, string $type, array $extraOptions = []) {
        $options = self::getUpdateActionOptions($entity, $baseUrl);
        $options = ArrayHelper::merge($options, $extraOptions);
        return self::generate($options, $type);
    }

    public static function generateRestoreAction(EntityIdInterface $entity, string $baseUrl, string $type, array $extraOptions = []) {
        $options = self::getRestoreActionOptions($entity, $baseUrl);
        $options = ArrayHelper::merge($options, $extraOptions);
        return self::generate($options, $type);
    }

    public static function generateDeleteAction(EntityIdInterface $entity, string $baseUrl, string $type, array $extraOptions = []) {
        $options = self::getDeleteActionOptions($entity, $baseUrl);
        $options = ArrayHelper::merge($options, $extraOptions);
        return self::generate($options, $type);
    }

    public static function generate(array $options, string $type) {
        if($type == 'button') {
            return self::generateButton($options);
        } else {
            return self::generateActionTag($options);
        }
    }

    public static function generateButton(array $options) {
        if(empty($options['class'])) {
            $options['class'] = '';
        }
        $options['class'] .= " btn ";
        if(!empty($options['type'])) {
            $options['class'] .= " btn-{$options['type']}";
        }
        if(empty($options['label'])) {
            $options['label'] = '';
        }
        if(isset($options['icon'])) {
            $options['label'] .= "<i class=\"{$options['icon']}\"></i>";
        }
        if(isset($options['title'])) {
            $options['label'] .= ' ' . $options['title'];
        }
        if(!empty($options['confirm'])) {
            $options['data-method'] = 'post';
            $options['data-confirm'] = $options['confirm'];
        }
        return self::tag('a', $options);
    }

    public static function generateActionTag(array $options, string $type = null, string $confirm = null) {
        $options['class'] = "text-decoration-none";
        if(!empty($options['type'])) {
            $options['class'] .= " text-{$options['type']}";
        }
        if(empty($options['label'])) {
            $options['label'] = '';
        }
        if(isset($options['icon'])) {
            $options['label'] .= "<i class=\"{$options['icon']}\"></i>";
        }
        if(!empty($options['confirm'])) {
            $options['data-method'] = 'post';
            $options['data-confirm'] = $options['confirm'];
        }
        return self::tag('a', $options);
    }

    public static function tag(string $tag, array $options): string {
        $content = $options['label'];
        unset($options['label']);
        return Html::tag($tag ,$content, $options);
    }
}
