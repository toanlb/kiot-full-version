<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\rbac\Role;
use yii\helpers\ArrayHelper;

/**
 * RbacAssignment model
 * Manages assignment of permissions to roles
 */
class RbacAssignment extends Model
{
    /**
     * @var string The name of the role
     */
    public $role;
    
    /**
     * @var array Selected permissions
     */
    public $permissions = [];
    
    /**
     * @var array List of module permissions to assign
     */
    public $modules = [];
    
    /**
     * @var array List of controller permissions to assign
     */
    public $controllers = [];
    
    /**
     * @var array List of action permissions to assign
     */
    public $actions = [];
    
    /**
     * @var \yii\rbac\ManagerInterface Auth manager
     */
    private $_authManager;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->_authManager = Yii::$app->authManager;
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['role'], 'required'],
            [['role'], 'validateRole'],
            [['permissions', 'modules', 'controllers', 'actions'], 'safe'],
        ];
    }
    
    /**
     * Validates that the role exists
     */
    public function validateRole($attribute, $params)
    {
        if (!$this->_authManager->getRole($this->$attribute)) {
            $this->addError($attribute, 'Role does not exist.');
        }
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'role' => 'Role',
            'permissions' => 'Permissions',
            'modules' => 'Modules',
            'controllers' => 'Controllers',
            'actions' => 'Actions',
        ];
    }
    
    /**
     * Loads the permissions of a role
     * 
     * @param string $roleName Name of the role
     * @return bool Whether loading was successful
     */
    public function loadRolePermissions($roleName)
    {
        $this->role = $roleName;
        
        $role = $this->_authManager->getRole($roleName);
        if (!$role) {
            return false;
        }
        
        // Get all permissions assigned to this role
        $rolePermissions = $this->_authManager->getPermissionsByRole($roleName);
        $this->permissions = array_keys($rolePermissions);
        
        return true;
    }
    
    /**
     * Gets all available roles
     * 
     * @return array List of roles
     */
    public function getRoles()
    {
        return ArrayHelper::map($this->_authManager->getRoles(), 'name', 'description');
    }
    
    /**
     * Gets all available permissions
     * 
     * @return array List of permissions
     */
    public function getPermissions()
    {
        return ArrayHelper::map($this->_authManager->getPermissions(), 'name', 'description');
    }
    
    /**
     * Saves the role permissions
     * 
     * @return bool Whether the save was successful
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        
        $role = $this->_authManager->getRole($this->role);
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // Remove all existing permissions
            $this->_authManager->removeChildren($role);
            
            // Add selected permissions
            $allPermissions = [];
            
            // Add module-level permissions
            if (!empty($this->modules)) {
                $allPermissions = array_merge($allPermissions, $this->modules);
            }
            
            // Add controller-level permissions
            if (!empty($this->controllers)) {
                $allPermissions = array_merge($allPermissions, $this->controllers);
            }
            
            // Add action-level permissions
            if (!empty($this->actions)) {
                $allPermissions = array_merge($allPermissions, $this->actions);
            }
            
            // Add directly selected permissions
            if (!empty($this->permissions)) {
                $allPermissions = array_merge($allPermissions, $this->permissions);
            }
            
            // Remove duplicates
            $allPermissions = array_unique($allPermissions);
            
            // Add to role
            foreach ($allPermissions as $permissionName) {
                $permission = $this->_authManager->getPermission($permissionName);
                if ($permission) {
                    $this->_authManager->addChild($role, $permission);
                }
            }
            
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Error saving role permissions: ' . $e->getMessage(), 'rbac');
            return false;
        }
    }
    
    /**
     * Creates a new role
     * 
     * @param string $name Role name
     * @param string $description Role description
     * @return \yii\rbac\Role|null The new role or null on failure
     */
    public function createRole($name, $description)
    {
        if ($this->_authManager->getRole($name)) {
            Yii::error("Role {$name} already exists", 'rbac');
            return null;
        }
        
        try {
            $role = $this->_authManager->createRole($name);
            $role->description = $description;
            $this->_authManager->add($role);
            return $role;
        } catch (\Exception $e) {
            Yii::error('Error creating role: ' . $e->getMessage(), 'rbac');
            return null;
        }
    }
    
    /**
     * Updates an existing role
     * 
     * @param string $name Role name
     * @param string $description Role description
     * @return bool Whether the update was successful
     */
    public function updateRole($name, $description)
    {
        $role = $this->_authManager->getRole($name);
        if (!$role) {
            Yii::error("Role {$name} does not exist", 'rbac');
            return false;
        }
        
        try {
            $role->description = $description;
            $this->_authManager->update($name, $role);
            return true;
        } catch (\Exception $e) {
            Yii::error('Error updating role: ' . $e->getMessage(), 'rbac');
            return false;
        }
    }
    
    /**
     * Deletes a role
     * 
     * @param string $name Role name
     * @return bool Whether the deletion was successful
     */
    public function deleteRole($name)
    {
        $role = $this->_authManager->getRole($name);
        if (!$role) {
            Yii::error("Role {$name} does not exist", 'rbac');
            return false;
        }
        
        try {
            $this->_authManager->remove($role);
            return true;
        } catch (\Exception $e) {
            Yii::error('Error deleting role: ' . $e->getMessage(), 'rbac');
            return false;
        }
    }
    
    /**
     * Checks if a permission is assigned to a role
     * 
     * @param string $roleName Role name
     * @param string $permissionName Permission name
     * @return bool Whether the permission is assigned
     */
    public function isPermissionAssigned($roleName, $permissionName)
    {
        $role = $this->_authManager->getRole($roleName);
        $permission = $this->_authManager->getPermission($permissionName);
        
        if (!$role || !$permission) {
            return false;
        }
        
        return $this->_authManager->hasChild($role, $permission);
    }
}