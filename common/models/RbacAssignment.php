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
        Yii::info('==== BEGIN SAVE PERMISSIONS ====', 'rbac');
        
        if (!$this->validate()) {
            Yii::error('Validation failed: ' . json_encode($this->errors), 'rbac');
            return false;
        }
        
        $role = $this->_authManager->getRole($this->role);
        if (!$role) {
            Yii::error('Role not found: ' . $this->role, 'rbac');
            return false;
        }
        
        Yii::info('Role found: ' . $role->name, 'rbac');
        
        // Debug AuthManager
        Yii::info('AuthManager class: ' . get_class($this->_authManager), 'rbac');
        
        // Get existing permissions before
        $existingPermissions = $this->_authManager->getPermissionsByRole($role->name);
        Yii::info('Existing permissions count: ' . count($existingPermissions), 'rbac');
        
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // Remove all existing permissions
            Yii::info('Removing all existing permissions for role: ' . $role->name, 'rbac');
            $removeResult = $this->_authManager->removeChildren($role);
            Yii::info('removeChildren result: ' . ($removeResult ? 'true' : 'false'), 'rbac');
            
            // Check if permissions were removed
            $afterRemovePermissions = $this->_authManager->getPermissionsByRole($role->name);
            Yii::info('Permissions after removeChildren: ' . count($afterRemovePermissions), 'rbac');
            
            // Process inputs as arrays
            if (is_string($this->modules)) {
                $modulesArray = explode(',', $this->modules);
            } else {
                $modulesArray = is_array($this->modules) ? $this->modules : [];
            }
            
            if (is_string($this->controllers)) {
                $controllersArray = explode(',', $this->controllers);
            } else {
                $controllersArray = is_array($this->controllers) ? $this->controllers : [];
            }
            
            if (is_string($this->actions)) {
                $actionsArray = explode(',', $this->actions);
            } else {
                $actionsArray = is_array($this->actions) ? $this->actions : [];
            }
            
            if (is_string($this->permissions)) {
                $permissionsArray = explode(',', $this->permissions);
            } else {
                $permissionsArray = is_array($this->permissions) ? $this->permissions : [];
            }
            
            Yii::info('Modules array: ' . json_encode($modulesArray), 'rbac');
            Yii::info('Controllers array: ' . json_encode($controllersArray), 'rbac');
            Yii::info('Actions array: ' . json_encode($actionsArray), 'rbac');
            Yii::info('Direct permissions: ' . json_encode($permissionsArray), 'rbac');
            
            // Combine all permissions
            $allPermissions = array_merge([], $modulesArray, $controllersArray, $actionsArray, $permissionsArray);
            $allPermissions = array_filter(array_unique($allPermissions));
            
            Yii::info('Total unique permissions to add: ' . count($allPermissions), 'rbac');
            Yii::info('All permissions: ' . json_encode($allPermissions), 'rbac');
            
            // Try to add each permission
            $addedCount = 0;
            $failedPermissions = [];
            
            foreach ($allPermissions as $permissionName) {
                if (empty($permissionName)) continue;
                
                $permission = $this->_authManager->getPermission($permissionName);
                
                if (!$permission) {
                    Yii::warning('Permission does not exist: ' . $permissionName, 'rbac');
                    $failedPermissions[] = $permissionName . ' (not found)';
                    continue;
                }
                
                Yii::info('Adding permission: ' . $permissionName, 'rbac');
                
                try {
                    $addResult = $this->_authManager->addChild($role, $permission);
                    
                    if ($addResult) {
                        $addedCount++;
                        Yii::info('Permission added successfully: ' . $permissionName, 'rbac');
                    } else {
                        Yii::warning('Failed to add permission: ' . $permissionName, 'rbac');
                        $failedPermissions[] = $permissionName;
                    }
                } catch (\Exception $e) {
                    Yii::error('Exception adding permission ' . $permissionName . ': ' . $e->getMessage(), 'rbac');
                    $failedPermissions[] = $permissionName . ' (' . $e->getMessage() . ')';
                }
            }
            
            Yii::info('Added permissions count: ' . $addedCount . ' out of ' . count($allPermissions), 'rbac');
            
            if (!empty($failedPermissions)) {
                Yii::warning('Failed to add permissions: ' . json_encode($failedPermissions), 'rbac');
            }
            
            // Check permissions after adding
            $finalPermissions = $this->_authManager->getPermissionsByRole($role->name);
            Yii::info('Final permissions count: ' . count($finalPermissions), 'rbac');
            
            // Check directly in database
            $count = Yii::$app->db->createCommand('SELECT COUNT(*) FROM {{%auth_item_child}} WHERE parent=:parent', [
                ':parent' => $role->name
            ])->queryScalar();
            
            Yii::info('Database records count in auth_item_child: ' . $count, 'rbac');
            
            // Final success check
            if ($addedCount > 0 || count($finalPermissions) > 0) {
                $transaction->commit();
                Yii::info('Transaction committed', 'rbac');
                Yii::info('==== END SAVE PERMISSIONS: SUCCESS ====', 'rbac');
                return true;
            } else {
                $transaction->rollBack();
                Yii::error('No permissions were added, rolling back transaction', 'rbac');
                Yii::info('==== END SAVE PERMISSIONS: FAILED ====', 'rbac');
                return false;
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Exception in save(): ' . $e->getMessage() . "\n" . $e->getTraceAsString(), 'rbac');
            Yii::info('==== END SAVE PERMISSIONS: ERROR ====', 'rbac');
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
