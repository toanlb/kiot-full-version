<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\rbac\Item;
use common\components\ActionScanner;
use yii\helpers\ArrayHelper;
use common\models\RbacPermissionRoute;

/**
 * RbacScan model
 * Manages scanning and updating permissions in RBAC system
 */
class RbacScan extends Model
{
    /**
     * @var array List of modules to scan
     */
    public $modules = ['frontend', 'backend'];
    
    /**
     * @var bool Add only new permissions
     */
    public $onlyNew = true;

    /**
     * @var bool Create permission hierarchy
     */
    public $createHierarchy = true;

    /**
     * @var array Internal storage for scanned routes
     */
    private $_routes = [];

    /**
     * @var array Internal storage for new permissions
     */
    private $_newPermissions = [];

    /**
     * @var array Internal storage for existing permissions
     */
    private $_existingPermissions = [];

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
            [['modules'], 'required'],
            [['onlyNew', 'createHierarchy'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'modules' => 'Modules to Scan',
            'onlyNew' => 'Add Only New Permissions',
            'createHierarchy' => 'Create Permission Hierarchy',
        ];
    }

    /**
     * Scans the application for controllers and actions
     * 
     * @return array The scanned routes
     */
    public function scan()
    {
        // Create a scanner instance
        $scanner = new ActionScanner([
            'modules' => $this->modules
        ]);
        
        // Scan for routes
        $this->_routes = $scanner->scan();
        
        // Load existing permissions
        $this->_existingPermissions = ArrayHelper::map(
            $this->_authManager->getPermissions(),
            'name',
            'description'
        );
        
        // Identify new permissions
        $this->_newPermissions = [];
        foreach ($this->_routes as $controller => $data) {
            if (!isset($this->_existingPermissions[$controller])) {
                $this->_newPermissions[$controller] = $data['name'];
            }
            
            foreach ($data['actions'] as $action => $name) {
                if (!isset($this->_existingPermissions[$action])) {
                    $this->_newPermissions[$action] = $name;
                }
            }
        }
        
        return [
            'routes' => $this->_routes,
            'existingPermissions' => $this->_existingPermissions,
            'newPermissions' => $this->_newPermissions
        ];
    }

    /**
     * Updates permissions in the RBAC system based on scanned routes
     * 
     * @return array Statistics about the update process
     */
    public function updatePermissions()
    {
        if (empty($this->_routes)) {
            $this->scan();
        }
        
        $stats = [
            'created' => 0,
            'updated' => 0,
            'unchanged' => 0,
            'hierarchies' => 0
        ];
        
        $authManager = $this->_authManager;
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // Create or update permissions
            foreach ($this->_routes as $controller => $data) {
                $controllerPerm = $this->processPermission($controller, $data['name'], $stats);
                
                foreach ($data['actions'] as $action => $name) {
                    $actionPerm = $this->processPermission($action, $name, $stats);
                    
                    // Create hierarchy (controller -> action)
                    if ($this->createHierarchy && $controllerPerm && $actionPerm) {
                        if (!$authManager->hasChild($controllerPerm, $actionPerm)) {
                            $authManager->addChild($controllerPerm, $actionPerm);
                            $stats['hierarchies']++;
                        }
                    }
                }
            }
            
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        
        return $stats;
    }

    /**
     * Processes a single permission (creates or updates it)
     * 
     * @param string $name Permission name
     * @param string $description Permission description
     * @param array $stats Statistics array to update
     * @return \yii\rbac\Permission|null The permission object or null
     */
    protected function processPermission($name, $description, &$stats)
    {
        $authManager = $this->_authManager;
        
        // Check if permission already exists
        $permission = $authManager->getPermission($name);
        
        // If it's a new permission or we're updating all
        if (!$permission) {
            if ($this->onlyNew || !isset($this->_existingPermissions[$name])) {
                $permission = $authManager->createPermission($name);
                $permission->description = $description;
                $authManager->add($permission);
                $stats['created']++;
                
                // Track the permission route
                $this->trackPermissionRoute($name);
            }
        } else {
            // Update description if changed
            if ($permission->description !== $description) {
                $permission->description = $description;
                $authManager->update($name, $permission);
                $stats['updated']++;
                
                // Update the permission route
                $this->trackPermissionRoute($name);
            } else {
                $stats['unchanged']++;
            }
        }
        
        return $permission;
    }
    
    /**
     * Tracks a permission route in the database
     * 
     * @param string $permission Permission name
     * @return bool Whether the tracking succeeded
     */
    protected function trackPermissionRoute($permission)
    {
        // Parse the permission name
        $parts = explode('/', $permission);
        
        if (count($parts) < 2) {
            return false;
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
        
        // Remove trailing '*' from controller
        $controller = str_replace('/*', '', $controller);
        
        // Save the route
        return RbacPermissionRoute::saveRoute($permission, $module, $controller, $action, $isController);
    }

    /**
     * Gets the list of available routes organized by module and controller
     * 
     * @return array Organized routes
     */
    public function getOrganizedRoutes()
    {
        if (empty($this->_routes)) {
            $this->scan();
        }
        
        $result = [];
        
        foreach ($this->_routes as $controller => $data) {
            // Extract module and controller parts from permission name
            $parts = explode('/', $controller);
            $module = $parts[0];
            $controllerId = isset($parts[1]) ? $parts[1] : 'default';
            
            // Remove the wildcard from controller name
            $controllerId = str_replace('/*', '', $controllerId);
            
            // Initialize module if not exists
            if (!isset($result[$module])) {
                $result[$module] = [
                    'name' => ucfirst($module),
                    'controllers' => []
                ];
            }
            
            // Initialize controller if not exists
            if (!isset($result[$module]['controllers'][$controllerId])) {
                $result[$module]['controllers'][$controllerId] = [
                    'name' => $data['name'],
                    'permission' => $controller,
                    'actions' => []
                ];
            }
            
            // Add actions
            foreach ($data['actions'] as $action => $name) {
                $result[$module]['controllers'][$controllerId]['actions'][$action] = [
                    'name' => $name,
                    'permission' => $action
                ];
            }
        }
        
        return $result;
    }
}