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

            // Nếu là AJAX request, chỉ render phần scan-results
            if (Yii::$app->request->isAjax) {
                return $this->renderAjax('_scan_results', [
                    'scanResults' => $scanResults,
                ]);
            }
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

    public function actionCheckPermissions()
    {
        $permissions = [
            'backend/*',
            'backend/customer/*',
            'backend/customer/index',
            'backend/customer/view',
            'backend/customer/create',
            'backend/customer/update',
            'backend/customer/delete',
            // Thêm các quyền khác nếu cần
        ];

        $results = [];
        $authManager = Yii::$app->authManager;

        foreach ($permissions as $permName) {
            $permission = $authManager->getPermission($permName);
            $results[$permName] = [
                'exists' => $permission !== null,
                'details' => $permission ? [
                    'type' => $permission->type,
                    'description' => $permission->description,
                    'created_at' => $permission->createdAt,
                    'updated_at' => $permission->updatedAt,
                ] : null
            ];
        }

        // Kiểm tra trong DB
        $dbItems = Yii::$app->db->createCommand('SELECT name, type, description, created_at, updated_at FROM {{%auth_item}} WHERE name IN (' . implode(',', array_map(function ($p) {
            return "'$p'";
        }, $permissions)) . ')')->queryAll();

        $dbResults = [];
        foreach ($dbItems as $item) {
            $dbResults[$item['name']] = $item;
        }

        return $this->renderContent(
            "<h2>AuthManager getPermission Results:</h2>" .
                "<pre>" . print_r($results, true) . "</pre>" .
                "<h2>Direct DB Query Results:</h2>" .
                "<pre>" . print_r($dbResults, true) . "</pre>"
        );
    }

    public function actionTestAddPermission($roleName = 'admin', $permissionName = 'backend/customer/*')
    {
        $authManager = Yii::$app->authManager;

        $role = $authManager->getRole($roleName);
        if (!$role) {
            return "Role '$roleName' not found!";
        }

        $permission = $authManager->getPermission($permissionName);
        if (!$permission) {
            return "Permission '$permissionName' not found!";
        }

        // Check if already assigned
        $existingPerms = $authManager->getPermissionsByRole($roleName);
        $alreadyExists = isset($existingPerms[$permissionName]);

        $result = '';
        if ($alreadyExists) {
            $authManager->removeChild($role, $permission);
            $result .= "Permission removed. ";
        }

        // Try to add the permission
        $addResult = $authManager->addChild($role, $permission);

        // Check database
        $dbCount = Yii::$app->db->createCommand('SELECT COUNT(*) FROM {{%auth_item_child}} WHERE parent=:parent AND child=:child', [
            ':parent' => $roleName,
            ':child' => $permissionName
        ])->queryScalar();

        return "Add result: " . ($addResult ? 'Success' : 'Failed') .
            "<br>Database records: $dbCount" .
            "<br>Permission class: " . get_class($permission) .
            "<br>Role class: " . get_class($role);
    }

    public function actionSaveAssignments()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new RbacAssignment();

        // Debug: Log request data
        Yii::info('RBAC Save Assignments Request: ' . print_r(Yii::$app->request->post(), true), 'rbac');

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // Debug: Log model data
            Yii::info('RBAC Model Data: ' . print_r([
                'role' => $model->role,
                'modules' => $model->modules,
                'controllers' => $model->controllers,
                'actions' => $model->actions,
                'permissions' => $model->permissions,
            ], true), 'rbac');

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
