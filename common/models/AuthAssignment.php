<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "auth_assignment".
 *
 * @property string $item_name
 * @property string $user_id
 * @property int|null $created_at
 *
 * @property AuthItem $itemName
 */
class AuthAssignment extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth_assignment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['item_name', 'user_id'], 'required'],
            [['created_at'], 'integer'],
            [['item_name', 'user_id'], 'string', 'max' => 64],
            [['item_name', 'user_id'], 'unique', 'targetAttribute' => ['item_name', 'user_id']],
            [['item_name'], 'exist', 'skipOnError' => true, 'targetClass' => AuthItem::class, 'targetAttribute' => ['item_name' => 'name']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'item_name' => 'Quyền',
            'user_id' => 'Người dùng',
            'created_at' => 'Ngày tạo',
        ];
    }

    /**
     * Gets query for [[ItemName]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItemName()
    {
        return $this->hasOne(AuthItem::class, ['name' => 'item_name']);
    }

    /**
     * Gets query for [[User]] based on user_id.
     *
     * @return \yii\db\ActiveQuery|null
     */
    public function getUser()
    {
        if (is_numeric($this->user_id)) {
            return $this->hasOne(User::class, ['id' => 'user_id']);
        }
        return null;
    }

    /**
     * Assign a role to user
     * 
     * @param int|string $userId
     * @param string $roleName
     * @return bool
     */
    public static function assignRole($userId, $roleName)
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($roleName);
        
        if (!$role) {
            return false;
        }
        
        try {
            $auth->assign($role, $userId);
            return true;
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            return false;
        }
    }

    /**
     * Revoke a role from user
     * 
     * @param int|string $userId
     * @param string $roleName
     * @return bool
     */
    public static function revokeRole($userId, $roleName)
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($roleName);
        
        if (!$role) {
            return false;
        }
        
        try {
            $auth->revoke($role, $userId);
            return true;
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            return false;
        }
    }

    /**
     * Get user roles
     * 
     * @param int|string $userId
     * @return array
     */
    public static function getUserRoles($userId)
    {
        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($userId);
        
        return array_keys($roles);
    }

    /**
     * Check if user has role
     * 
     * @param int|string $userId
     * @param string $roleName
     * @return bool
     */
    public static function hasRole($userId, $roleName)
    {
        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($userId);
        
        return isset($roles[$roleName]);
    }
}