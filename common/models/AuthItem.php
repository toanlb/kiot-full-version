<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "auth_item".
 *
 * @property string $name
 * @property int $type
 * @property string|null $description
 * @property string|null $rule_name
 * @property resource|null $data
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property AuthAssignment[] $authAssignments
 * @property AuthItemChild[] $authItemChildren
 * @property AuthItemChild[] $authItemParents
 * @property AuthItem[] $children
 * @property AuthItem[] $parents
 * @property AuthRule $ruleName
 */
class AuthItem extends ActiveRecord
{
    const TYPE_ROLE = 1;
    const TYPE_PERMISSION = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['type', 'created_at', 'updated_at'], 'integer'],
            [['description', 'data'], 'string'],
            [['name', 'rule_name'], 'string', 'max' => 64],
            [['name'], 'unique'],
            [['rule_name'], 'exist', 'skipOnError' => true, 'targetClass' => AuthRule::class, 'targetAttribute' => ['rule_name' => 'name']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Tên',
            'type' => 'Loại',
            'description' => 'Mô tả',
            'rule_name' => 'Rule',
            'data' => 'Dữ liệu',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        ];
    }

    /**
     * Gets query for [[AuthAssignments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::class, ['item_name' => 'name']);
    }

    /**
     * Gets query for [[AuthItemChildren]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemChildren()
    {
        return $this->hasMany(AuthItemChild::class, ['parent' => 'name']);
    }

    /**
     * Gets query for [[AuthItemParents]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemParents()
    {
        return $this->hasMany(AuthItemChild::class, ['child' => 'name']);
    }

    /**
     * Gets query for [[Children]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(AuthItem::class, ['name' => 'child'])->via('authItemChildren');
    }

    /**
     * Gets query for [[Parents]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParents()
    {
        return $this->hasMany(AuthItem::class, ['name' => 'parent'])->via('authItemParents');
    }

    /**
     * Gets query for [[RuleName]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRuleName()
    {
        return $this->hasOne(AuthRule::class, ['name' => 'rule_name']);
    }

    /**
     * Get type label
     * 
     * @return string
     */
    public function getTypeLabel()
    {
        $types = self::getTypes();
        return isset($types[$this->type]) ? $types[$this->type] : 'Không xác định';
    }

    /**
     * Get types
     * 
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_ROLE => 'Vai trò',
            self::TYPE_PERMISSION => 'Quyền',
        ];
    }

    /**
     * Get all roles
     * 
     * @return array
     */
    public static function getAllRoles()
    {
        return self::find()->where(['type' => self::TYPE_ROLE])->all();
    }

    /**
     * Get all permissions
     * 
     * @return array
     */
    public static function getAllPermissions()
    {
        return self::find()->where(['type' => self::TYPE_PERMISSION])->all();
    }

    /**
     * Create a role
     * 
     * @param string $name
     * @param string $description
     * @return bool
     */
    public static function createRole($name, $description = null)
    {
        $auth = Yii::$app->authManager;
        $role = $auth->createRole($name);
        
        if ($description) {
            $role->description = $description;
        }
        
        try {
            $auth->add($role);
            return true;
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            return false;
        }
    }

    /**
     * Create a permission
     * 
     * @param string $name
     * @param string $description
     * @return bool
     */
    public static function createPermission($name, $description = null)
    {
        $auth = Yii::$app->authManager;
        $permission = $auth->createPermission($name);
        
        if ($description) {
            $permission->description = $description;
        }
        
        try {
            $auth->add($permission);
            return true;
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            return false;
        }
    }

    /**
     * Add child
     * 
     * @param string $parent
     * @param string $child
     * @return bool
     */
    public static function addChild($parent, $child)
    {
        $auth = Yii::$app->authManager;
        $parentRole = $auth->getRole($parent) ?: $auth->getPermission($parent);
        $childRole = $auth->getRole($child) ?: $auth->getPermission($child);
        
        if (!$parentRole || !$childRole) {
            return false;
        }
        
        try {
            $auth->addChild($parentRole, $childRole);
            return true;
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            return false;
        }
    }

    /**
     * Remove child
     * 
     * @param string $parent
     * @param string $child
     * @return bool
     */
    public static function removeChild($parent, $child)
    {
        $auth = Yii::$app->authManager;
        $parentRole = $auth->getRole($parent) ?: $auth->getPermission($parent);
        $childRole = $auth->getRole($child) ?: $auth->getPermission($child);
        
        if (!$parentRole || !$childRole) {
            return false;
        }
        
        try {
            $auth->removeChild($parentRole, $childRole);
            return true;
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            return false;
        }
    }
}