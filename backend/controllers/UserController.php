<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use common\models\UserProfile;
use common\models\LoginHistory;
use common\models\UserWarehouse;
use common\models\Warehouse;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use yii\web\Response;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'login-history', 'reset-password'],
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'reset-password' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => User::find()->where(['!=', 'status', User::STATUS_DELETED]),
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        // Load user profile
        $profile = $model->userProfile ?: new UserProfile(['user_id' => $id]);
        
        // Load assigned warehouses
        $assignedWarehouses = ArrayHelper::map(
            UserWarehouse::find()->where(['user_id' => $id])->all(),
            'warehouse_id',
            'warehouse_id'
        );
        
        // Get all available roles
        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($id);
        
        // Get all warehouses
        $warehouses = Warehouse::find()->where(['is_active' => 1])->all();
        
        return $this->render('view', [
            'model' => $model,
            'profile' => $profile,
            'assignedWarehouses' => $assignedWarehouses,
            'roles' => $roles,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Creates a new User model.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();
        $profile = new UserProfile();
        
        // Set default status
        $model->status = User::STATUS_ACTIVE;
        
        // Get all available warehouses
        $warehouses = Warehouse::find()->where(['is_active' => 1])->all();
        
        // Get auth manager for roles
        $auth = Yii::$app->authManager;
        $availableRoles = $auth->getRoles();
        
        if ($model->load(Yii::$app->request->post()) && $profile->load(Yii::$app->request->post())) {
            // Handle avatar upload
            $avatarFile = UploadedFile::getInstance($model, 'avatar');
            if ($avatarFile) {
                $uploadDir = 'uploads/avatars/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $model->avatar = $uploadDir . uniqid() . '.' . $avatarFile->extension;
            }
            
            // Set password and generate auth key
            if (!empty($model->password)) {
                $model->setPassword($model->password);
            } else {
                // Default password if not provided
                $model->setPassword('password');
            }
            $model->generateAuthKey();
            
            // Start transaction
            $transaction = Yii::$app->db->beginTransaction();
            
            try {
                if ($model->save()) {
                    // Save profile
                    $profile->user_id = $model->id;
                    $profile->save();
                    
                    // Upload avatar file if exists
                    if ($avatarFile) {
                        $avatarFile->saveAs($model->avatar);
                    }
                    
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Người dùng đã được tạo thành công.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            }
        }
        
        return $this->render('create', [
            'model' => $model,
            'profile' => $profile,
            'warehouses' => $warehouses,
            'availableRoles' => $availableRoles,
            'assignedWarehouses' => [],
        ]);
    }

    /**
     * Updates an existing User model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $profile = $model->userProfile ?: new UserProfile(['user_id' => $id]);
        
        // Get all available warehouses
        $warehouses = Warehouse::find()->where(['is_active' => 1])->all();
        
        // Get current assigned warehouses
        $assignedWarehouses = ArrayHelper::map(
            UserWarehouse::find()->where(['user_id' => $id])->all(),
            'warehouse_id',
            'warehouse_id'
        );
        
        // Get all available roles
        $auth = Yii::$app->authManager;
        $availableRoles = $auth->getRoles();
        
        // Save old avatar
        $oldAvatar = $model->avatar;
        
        if ($model->load(Yii::$app->request->post()) && $profile->load(Yii::$app->request->post())) {
            // Handle avatar upload
            $avatarFile = UploadedFile::getInstance($model, 'avatar');
            if ($avatarFile) {
                $uploadDir = 'uploads/avatars/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $model->avatar = $uploadDir . uniqid() . '.' . $avatarFile->extension;
            } else {
                $model->avatar = $oldAvatar;
            }
            
            // Update password only if provided
            if (!empty($model->password)) {
                $model->setPassword($model->password);
            }
            
            // Start transaction
            $transaction = Yii::$app->db->beginTransaction();
            
            try {
                if ($model->save()) {
                    // Save profile
                    $profile->user_id = $model->id;
                    $profile->save();
                    
                    // Upload avatar file if exists
                    if ($avatarFile) {
                        $avatarFile->saveAs($model->avatar);
                        // Delete old avatar if exists
                        if ($oldAvatar && file_exists($oldAvatar) && $oldAvatar != $model->avatar) {
                            unlink($oldAvatar);
                        }
                    }
                    
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Người dùng đã được cập nhật thành công.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            }
        } else {
            // Populate roles for form
            $model->roles = $model->getRoles();
            // Populate warehouses for form
            $model->warehouses = $assignedWarehouses;
        }
        
        return $this->render('update', [
            'model' => $model,
            'profile' => $profile,
            'warehouses' => $warehouses,
            'availableRoles' => $availableRoles,
            'assignedWarehouses' => $assignedWarehouses,
        ]);
    }

    /**
     * Deletes an existing User model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        // Prevent deleting own account
        if ($id == Yii::$app->user->id) {
            Yii::$app->session->setFlash('error', 'Không thể xóa tài khoản của chính mình.');
            return $this->redirect(['index']);
        }
        
        $model = $this->findModel($id);
        
        // Instead of deleting, set status to deleted
        $model->status = User::STATUS_DELETED;
        $model->save(false);
        
        Yii::$app->session->setFlash('success', 'Người dùng đã được xóa thành công.');
        return $this->redirect(['index']);
    }

    /**
     * Reset user password
     * @param integer $id
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionResetPassword($id)
    {
        $model = $this->findModel($id);
        $newPassword = Yii::$app->security->generateRandomString(8);
        
        $model->setPassword($newPassword);
        
        if ($model->save(false)) {
            Yii::$app->session->setFlash('success', 'Mật khẩu mới: ' . $newPassword);
        } else {
            Yii::$app->session->setFlash('error', 'Không thể đặt lại mật khẩu.');
        }
        
        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Display login history for a user
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionLoginHistory($id)
    {
        $model = $this->findModel($id);
        
        $dataProvider = new ActiveDataProvider([
            'query' => LoginHistory::find()->where(['user_id' => $id])->orderBy(['login_time' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('login-history', [
            'dataProvider' => $dataProvider,
            'user' => $model,
        ]);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Không tìm thấy người dùng.');
    }
}