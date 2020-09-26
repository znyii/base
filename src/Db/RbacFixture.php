<?php

namespace ZnYii\Base\Db;

use Yii;
use yii\rbac\Item;
use ZnCore\Base\Exceptions\NotFoundException;
use ZnCore\Base\Helpers\EnumHelper;
use ZnCore\Db\Fixture\Libs\FixtureInterface;
use ZnYii\App\BootstrapYii;
use ZnYii\App\Enums\AppTypeEnum;

abstract class RbacFixture implements FixtureInterface
{

    private $authManager;

    public function __construct()
    {
        BootstrapYii::init('console', AppTypeEnum::CONSOLE);
        $this->authManager = Yii::$app->authManager;
    }

    public function deps()
    {
        return [];
    }

    public function load()
    {
        // Создание ролей
        foreach ($this->roleEnums() as $roleEnumClass) {
            $this->loadRolesFromEnum($roleEnumClass);
        }
        // Создание полномочий
        foreach ($this->permissionEnums() as $permissionEnumClass) {
            $this->loadPermissionsFromEnum($permissionEnumClass);
        }
        // Наследование
        foreach ($this->map() as $parentName => $childrenNames) {
            $this->addChildren($parentName, $childrenNames);
        }
    }

    public function unload()
    {
        $this->authManager->removeAll();
    }

    protected function loadRolesFromEnum(string $enumClassName)
    {
        $itemNames = EnumHelper::getValues($enumClassName);
        foreach ($itemNames as $itemName) {
            $label = EnumHelper::getLabel($enumClassName, $itemName);
            $this->addRole($itemName, $label);
        }
    }

    protected function loadPermissionsFromEnum(string $enumClassName)
    {
        $itemNames = EnumHelper::getValues($enumClassName);
        foreach ($itemNames as $itemName) {
            $label = EnumHelper::getLabel($enumClassName, $itemName);
            $this->addPermission($itemName, $label);
        }
    }

    protected function assign(string $roleName, int $userId)
    {
        $role = $this->getItem($roleName);
        $this->authManager->assign($role, $userId);
    }

    protected function addChildren(string $parentName, array $childNames)
    {
        foreach ($childNames as $childName) {
            $this->addChild($parentName, $childName);
        }
    }

    protected function addChild(string $parentName, string $childName)
    {
        $parent = $this->getItem($parentName);
        $child = $this->getItem($childName);
        $this->authManager->addChild($parent, $child);
    }

    protected function getItem(string $name): Item
    {
        $item = $this->authManager->getRole($name) ?? $this->authManager->getPermission($name);
        if ($item == null) {
            throw new NotFoundException('RBAC item "' . $name . '" not found!');
        }
        return $item;
    }

    protected function addRole(string $name, string $description): bool
    {
        $item = $this->authManager->createRole($name);
        $item->description = $description;
        return $this->authManager->add($item);
    }

    protected function addPermission(string $name, string $description): bool
    {
        $item = $this->authManager->createPermission($name);
        $item->description = $description;
        return $this->authManager->add($item);
    }
}
