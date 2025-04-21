<?php

namespace common\components;

use Yii;
use yii\base\Component;
use yii\helpers\FileHelper;
use yii\base\InvalidConfigException;
use ReflectionClass;
use ReflectionMethod;

/**
 * ActionScanner is a component that scans all controllers in the system
 * and retrieves their actions for RBAC permission management.
 */
class ActionScanner extends Component
{
    /**
     * @var array List of modules to scan [frontend, backend, api, etc.]
     */
    public $modules = ['frontend', 'backend'];

    /**
     * @var array List of controller namespaces to scan
     */
    public $controllerNamespaces = [];

    /**
     * @var array List of controllers to exclude from scanning
     */
    public $excludeControllers = ['debug', 'gii'];

    /**
     * @var array List of controller paths to scan
     */
    public $controllerPaths = [];

    /**
     * @var array Cache of scanned routes
     */
    private $_routes = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Initialize default namespaces if empty
        if (empty($this->controllerNamespaces)) {
            foreach ($this->modules as $module) {
                $this->controllerNamespaces[] = "$module\\controllers";
            }
        }

        // Initialize default controller paths if empty
        if (empty($this->controllerPaths)) {
            foreach ($this->modules as $module) {
                $this->controllerPaths[] = Yii::getAlias("@$module/controllers");
            }
        }
    }

    /**
     * Scans all controllers and returns a list of available actions
     * 
     * @return array List of controllers and their actions
     */
    public function scan()
    {
        $this->_routes = [];

        // Scan each controller path
        foreach ($this->controllerPaths as $index => $path) {
            $namespace = isset($this->controllerNamespaces[$index]) ? 
                $this->controllerNamespaces[$index] : '';
                
            $this->scanControllerDirectory($path, $namespace);
        }

        return $this->_routes;
    }

    /**
     * Scans a controller directory and its subdirectories
     * 
     * @param string $directory The directory to scan
     * @param string $namespace The namespace of the controllers
     * @param string $prefix The module prefix for permissions
     * @return void
     */
    protected function scanControllerDirectory($directory, $namespace, $prefix = '')
    {
        if (!is_dir($directory)) {
            return;
        }

        // Get all controller files in the directory
        $files = FileHelper::findFiles($directory, [
            'only' => ['*Controller.php'],
            'recursive' => true
        ]);

        foreach ($files as $file) {
            $relativePath = str_replace($directory, '', $file);
            $relativePath = ltrim($relativePath, '/\\');
            $subNamespace = '';
            
            // Handle subdirectories/modules
            if (strpos($relativePath, DIRECTORY_SEPARATOR) !== false) {
                $parts = explode(DIRECTORY_SEPARATOR, $relativePath);
                $controller = array_pop($parts);
                $subNamespace = '\\' . implode('\\', $parts);
                $modulePrefix = strtolower(implode('/', $parts));
                $prefix = $modulePrefix ? $modulePrefix . '/' : '';
            } else {
                $controller = $relativePath;
            }

            $this->scanControllerFile($controller, $namespace . $subNamespace, $prefix);
        }
    }

    /**
     * Scans a controller file and extracts its actions
     * 
     * @param string $file The controller file name
     * @param string $namespace The namespace of the controller
     * @param string $prefix The module prefix for permissions
     * @return void
     */
    protected function scanControllerFile($file, $namespace, $prefix = '')
    {
        // Extract the controller name
        $controller = substr($file, 0, -4); // remove .php
        
        // Check if it's in excluded list
        foreach ($this->excludeControllers as $exclude) {
            if (stripos($controller, $exclude) !== false) {
                return;
            }
        }
        
        // Form the fully qualified class name
        $className = $namespace . '\\' . $controller;
        
        try {
            // Check if the class exists and is a controller
            if (!class_exists($className)) {
                return;
            }
            
            $reflection = new ReflectionClass($className);
            
            // Skip if it's abstract or not a controller
            if ($reflection->isAbstract() || !$reflection->isSubclassOf('yii\web\Controller')) {
                return;
            }
            
            // Get the controller ID from the class name
            $controllerId = $this->getControllerId($controller);
            
            // Get all action methods from the controller
            $actions = $this->getControllerActions($reflection);
            
            if (!empty($actions)) {
                $module = $this->getModuleFromNamespace($namespace);
                $permissionPrefix = $module ? $module . '/' : '';
                $routePrefix = $prefix ? $prefix . '/' : '';
                
                // Create controller-level permission
                $controllerPermission = $permissionPrefix . $routePrefix . $controllerId . '/*';
                
                // Store the controller route
                $this->_routes[$controllerPermission] = [
                    'name' => $this->getReadableName($controller),
                    'actions' => []
                ];
                
                // Store each action route
                foreach ($actions as $action) {
                    $actionPermission = $permissionPrefix . $routePrefix . $controllerId . '/' . $action;
                    $this->_routes[$controllerPermission]['actions'][$actionPermission] = $this->getReadableName($action);
                }
            }
        } catch (\Exception $e) {
            Yii::warning("Error scanning controller $className: " . $e->getMessage(), 'rbac');
        }
    }

    /**
     * Extracts the controller ID from the controller class name
     * 
     * @param string $controller The controller class name
     * @return string The controller ID
     */
    protected function getControllerId($controller)
    {
        // Remove 'Controller' suffix and convert to kebab-case
        $id = substr($controller, 0, -10); // remove 'Controller'
        $id = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $id));
        return strtolower($id);
    }

    /**
     * Gets all action methods from a controller
     * 
     * @param ReflectionClass $reflection The controller reflection
     * @return array List of action IDs
     */
    protected function getControllerActions(ReflectionClass $reflection)
    {
        $actions = [];
        
        // Get all public methods
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        
        foreach ($methods as $method) {
            $name = $method->getName();
            
            // Check if it's an action method (starts with 'action')
            if (strpos($name, 'action') === 0 && $name !== 'actions') {
                $actionId = lcfirst(substr($name, 6)); // remove 'action' prefix
                if (!empty($actionId)) {
                    $actions[] = $actionId;
                }
            }
        }
        
        return $actions;
    }

    /**
     * Extracts the module name from a namespace
     * 
     * @param string $namespace The namespace string
     * @return string|null The module name or null
     */
    protected function getModuleFromNamespace($namespace)
    {
        foreach ($this->modules as $module) {
            if (strpos($namespace, $module) === 0) {
                return $module;
            }
        }
        
        return null;
    }

    /**
     * Converts a camelCase or kebab-case string to a readable name
     * 
     * @param string $name The name to convert
     * @return string The readable name
     */
    protected function getReadableName($name)
    {
        // Remove 'Controller' suffix if present
        $name = str_replace('Controller', '', $name);
        
        // Convert camelCase to spaces
        $name = preg_replace('/(?<!^)[A-Z]/', ' $0', $name);
        
        // Convert kebab-case to spaces
        $name = str_replace('-', ' ', $name);
        
        return ucwords(trim($name));
    }
}