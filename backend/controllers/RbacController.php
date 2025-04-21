<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use common\models\RbacScan;
use common\models\RbacAssignment;

/**
 * RbacController implements the actions for RBAC management.
 */
class RbacController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete-role' => ['post'],
                    'update-permissions' => ['post'],
                    'save-assignments' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all roles.
     * 
     * @return mixed
     */
    public function actionIndex()
    {
        $authManager = Yii::$app->authManager;
        $roles = $authManager->getRoles();
        
        return $this->render('index', [
            'roles' => $roles,
        ]);
    }

    /**
     * Shows all permissions.
     * 
     * @return mixed
     */
    public function actionPermissions()
    {
        $authManager = Yii::$app->authManager;
        $permissions = $authManager->getPermissions();
        
        return $this->render('permissions', [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Scans the system for controllers and actions.
     * 
     * @return mixed
     */
    public function actionScan()
    {
        $model = new RbacScan();
        $scanResults = null;
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $scanResults = $model->scan();
        }
        
        return $this->render('scan', [
            'model' => $model,
            'scanResults' => $scanResults,
        ]);
    }

    /**
     * Updates RBAC permissions based on scan results.
     * 
     * @return mixed
     */
    public function actionUpdatePermissions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $model = new RbacScan();
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                $stats = $model->updatePermissions();
                return [
                    'success' => true,
                    'message' => 'Permissions updated successfully',
                    'stats' => $stats,
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Error updating permissions: ' . $e->getMessage(),
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => 'Invalid input data',
        ];
    }

    /**
     * Creates a new role.
     * 
     * @return mixed
     */
    public function actionCreateRole()
    {
        $model = new RbacAssignment();
        
        if (Yii::$app->request->isPost) {
            $name = Yii::$app->request->post('name');
            $description = Yii::$app->request->post('description');
            
            if ($name && $description) {
                $role = $model->createRole($name, $description);
                
                if ($role) {
                    Yii::$app->session->setFlash('success', "Role {$name} created successfully.");
                    return $this->redirect(['index']);
                } else {
                    Yii::$app->session->setFlash('error', "Failed to create role {$name}.");
                }
            }
        }
        
        return $this->render('create-role', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing role.
     * 
     * @param string $name Role name
     * @return mixed
     * @throws NotFoundHttpException if the role cannot be found
     */
    public function actionUpdateRole($name)
    {
        $authManager = Yii::$app->authManager;
        $role = $authManager->getRole($name);
        
        if (!$role) {
            throw new NotFoundHttpException("Role {$name} not found.");
        }
        
        $model = new RbacAssignment();
        
        if (Yii::$app->request->isPost) {
            $description = Yii::$app->request->post('description');
            
            if ($description) {
                if ($model->updateRole($name, $description)) {
                    Yii::$app->session->setFlash('success', "Role {$name} updated successfully.");
                    return $this->redirect(['index']);
                } else {
                    Yii::$app->session->setFlash('error', "Failed to update role {$name}.");
                }
            }
        }
        
        return $this->render('update-role', [
            'model' => $model,
            'role' => $role,
        ]);
    }

    /**
     * Deletes a role.
     * 
     * @param string $name Role name
     * @return mixed
     */
    public function actionDeleteRole($name)
    {
        $model = new RbacAssignment();
        
        if ($model->deleteRole($name)) {
            Yii::$app->session->setFlash('success', "Role {$name} deleted successfully.");
        } else {
            Yii::$app->session->setFlash('error', "Failed to delete role {$name}.");
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Assigns permissions to a role.
     * 
     * @param string $name Role name
     * @return mixed
     * @throws NotFoundHttpException if the role cannot be found
     */
    public function actionAssign($name = null)
    {
        $model = new RbacAssignment();
        $scanModel = new RbacScan();
        
        if ($name !== null) {
            if (!$model->loadRolePermissions($name)) {
                throw new NotFoundHttpException("Role {$name} not found.");
            }
        }
        
        // Get organized routes
        $routes = $scanModel->getOrganizedRoutes();
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', "Permissions for role {$model->role} saved successfully.");
                return $this->redirect(['assign', 'name' => $model->role]);
            } else {
                Yii::$app->session->setFlash('error', "Failed to save permissions for role {$model->role}.");
            }
        }
        
        return $this->render('assign', [
            'model' => $model,
            'routes' => $routes,
            'roles' => $model->getRoles(),
        ]);
    }

    /**
     * Saves permission assignments for a role.
     * 
     * @return mixed
     */
    public function actionSaveAssignments()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $model = new RbacAssignment();
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                return [
                    'success' => true,
                    'message' => "Permissions for role {$model->role} saved successfully.",
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Failed to save permissions for role {$model->role}.",
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => 'Invalid input data',
            'errors' => $model->errors,
        ];
    }
}