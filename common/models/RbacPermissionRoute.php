<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "rbac_permission_route".
 *
 * @property int $id
 * @property string $permission_name
 * @property string $module
 * @property string $controller
 * @property string|null $action
 * @property int $is_controller
 * @property int $created_at
 * @property int $updated_at
 */
class RbacPermissionRoute extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%rbac_permission_route}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['permission_name', 'module', 'controller'], 'required'],
            [['is_controller'], 'boolean'],
            [['created_at', 'updated_at'], 'integer'],
            [['permission_name'], 'string', 'max' => 128],
            [['module', 'controller', 'action'], 'string', 'max' => 64],
            [['permission_name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'permission_name' => 'Permission Name',
            'module' => 'Module',
            'controller' => 'Controller',
            'action' => 'Action',
            'is_controller' => 'Is Controller',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Saves a permission route for tracking
     * 
     * @param string $permission Permission name
     * @param string $module Module name
     * @param string $controller Controller name
     * @param string|null $action Action name
     * @param bool $isController Whether this is a controller permission
     * @return bool Whether the operation succeeded
     */
    public static function saveRoute($permission, $module, $controller, $action = null, $isController = false)
    {
        $model = self::findOne(['permission_name' => $permission]);
        
        if (!$model) {
            $model = new self();
            $model->permission_name = $permission;
        }
        
        $model->module = $module;
        $model->controller = $controller;
        $model->action = $action;
        $model->is_controller = $isController;
        
        return $model->save();
    }

    /**
     * Gets routes by module
     * 
     * @param string $module
     * @return array
     */
    public static function getRoutesByModule($module)
    {
        return self::find()
            ->where(['module' => $module])
            ->orderBy(['controller' => SORT_ASC, 'action' => SORT_ASC])
            ->all();
    }

    /**
     * Gets routes by controller
     * 
     * @param string $module
     * @param string $controller
     * @return array
     */
    public static function getRoutesByController($module, $controller)
    {
        return self::find()
            ->where(['module' => $module, 'controller' => $controller])
            ->orderBy(['action' => SORT_ASC])
            ->all();
    }

    /**
     * Gets all controller routes
     * 
     * @return array
     */
    public static function getControllerRoutes()
    {
        return self::find()
            ->where(['is_controller' => true])
            ->orderBy(['module' => SORT_ASC, 'controller' => SORT_ASC])
            ->all();
    }

    /**
     * Gets all action routes
     * 
     * @return array
     */
    public static function getActionRoutes()
    {
        return self::find()
            ->where(['is_controller' => false])
            ->orderBy(['module' => SORT_ASC, 'controller' => SORT_ASC, 'action' => SORT_ASC])
            ->all();
    }

    /**
     * Parses a permission name into module, controller, and action
     * 
     * @param string $permission
     * @return array|null
     */
    public static function parsePermission($permission)
    {
        $parts = explode('/', $permission);
        
        if (count($parts) < 2) {
            return null;
        }
        
        $module = $parts[0];
        $controller = $parts[1];
        $action = null;
        $isController = false;
        
        if (count($parts) > 2) {
            if ($parts[2] === '*') {
                $isController = true;
            } else {
                $action = $parts[2];
            }
        }
        
        return [
            'module' => $module,
            'controller' => $controller,
            'action' => $action,
            'is_controller' => $isController,
        ];
    }
}