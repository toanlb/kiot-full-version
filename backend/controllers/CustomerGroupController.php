<?php

namespace backend\controllers;

use Yii;
use common\models\CustomerGroup;
use common\models\CustomerGroupSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\helpers\Json;
use common\models\Customer;

/**
 * CustomerGroupController implements the CRUD actions for CustomerGroup model.
 */
class CustomerGroupController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'customer-list'],
                        'allow' => true,
                        'roles' => ['@'], // Yêu cầu người dùng đã đăng nhập
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all CustomerGroup models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CustomerGroupSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CustomerGroup model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        // Tạo data provider cho danh sách khách hàng trong nhóm
        $customersDataProvider = new ActiveDataProvider([
            'query' => $model->getCustomers(),
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => [
                    'name' => SORT_ASC,
                ]
            ],
        ]);

        return $this->render('view', [
            'model' => $model,
            'customersDataProvider' => $customersDataProvider,
        ]);
    }

    /**
     * Creates a new CustomerGroup model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CustomerGroup();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Nhóm khách hàng đã được tạo thành công.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing CustomerGroup model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Nhóm khách hàng đã được cập nhật thành công.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing CustomerGroup model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        // Kiểm tra xem có khách hàng nào đang thuộc nhóm này không
        $customerCount = $model->getCustomerCount();
        
        if ($customerCount > 0) {
            Yii::$app->session->setFlash('error', "Không thể xóa nhóm '{$model->name}' vì có {$customerCount} khách hàng đang thuộc nhóm này.");
            return $this->redirect(['index']);
        }
        
        try {
            $model->delete();
            Yii::$app->session->setFlash('success', 'Nhóm khách hàng đã được xóa thành công.');
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Không thể xóa nhóm khách hàng: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }
    
    /**
     * Lists customers in a group.
     * @param integer $id
     * @return mixed
     */
    public function actionCustomerList($id)
    {
        $model = $this->findModel($id);
        
        $dataProvider = new ActiveDataProvider([
            'query' => Customer::find()->where(['customer_group_id' => $id]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        
        return $this->render('customer-list', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Finds the CustomerGroup model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CustomerGroup the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CustomerGroup::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Không tìm thấy nhóm khách hàng được yêu cầu.');
    }
}