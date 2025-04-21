<?php

namespace backend\controllers;

use Yii;
use common\models\Warranty;
use common\models\WarrantyDetail;
use common\models\Product;
use common\models\Customer;
use common\models\WarrantyStatus;
use common\models\WarrantySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\components\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * WarrantyController implements the CRUD actions for Warranty model.
 */
class WarrantyController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'get-product-info', 'get-customer-info', 'report', 'print'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Warranty models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new WarrantySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Prepare dropdown data for filters
        $statuses = ArrayHelper::map(WarrantyStatus::find()->all(), 'id', 'name');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Displays a single Warranty model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $warrantyDetail = new WarrantyDetail();
        $warrantyDetail->warranty_id = $id;
        $warrantyDetail->service_date = date('Y-m-d H:i:s');
        
        // Get existing warranty details
        $warrantyDetails = WarrantyDetail::find()
            ->where(['warranty_id' => $id])
            ->orderBy(['service_date' => SORT_DESC])
            ->all();
        
        // Process warranty detail form submission
        if ($warrantyDetail->load(Yii::$app->request->post())) {
            $warrantyDetail->handled_by = Yii::$app->user->id;
            $warrantyDetail->created_at = date('Y-m-d H:i:s');
            
            // Calculate total cost if not provided
            if (empty($warrantyDetail->total_cost)) {
                $warrantyDetail->total_cost = (float)$warrantyDetail->replacement_cost + (float)$warrantyDetail->service_cost;
            }
            
            if ($warrantyDetail->save()) {
                // Update warranty status
                $model->status_id = $warrantyDetail->status_id;
                $model->save();
                
                Yii::$app->session->setFlash('success', 'Warranty detail has been added successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        
        // Prepare dropdown data
        $statuses = ArrayHelper::map(WarrantyStatus::find()->all(), 'id', 'name');
        $replacementProducts = ArrayHelper::map(Product::find()->all(), 'id', 'name');
        
        return $this->render('view', [
            'model' => $model,
            'warrantyDetail' => $warrantyDetail,
            'warrantyDetails' => $warrantyDetails,
            'statuses' => $statuses,
            'replacementProducts' => $replacementProducts,
        ]);
    }

    /**
     * Creates a new Warranty model.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Warranty();
        $model->active = true;
        $model->created_at = date('Y-m-d H:i:s');
        $model->updated_at = date('Y-m-d H:i:s');
        $model->created_by = Yii::$app->user->id;

        // Generate unique warranty code
        $model->code = 'WR'.date('ymd').'-'.rand(1000, 9999);
        
        if ($model->load(Yii::$app->request->post())) {
            // Set default status if not selected
            if (empty($model->status_id)) {
                $defaultStatus = WarrantyStatus::find()->orderBy(['sort_order' => SORT_ASC])->one();
                if ($defaultStatus) {
                    $model->status_id = $defaultStatus->id;
                }
            }
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Warranty has been created successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        // Prepare dropdown data
        $products = ArrayHelper::map(Product::find()->where(['>', 'warranty_period', 0])->all(), 'id', 'name');
        $customers = ArrayHelper::map(Customer::find()->all(), 'id', 'name');
        $statuses = ArrayHelper::map(WarrantyStatus::find()->all(), 'id', 'name');
        
        return $this->render('create', [
            'model' => $model,
            'products' => $products,
            'customers' => $customers,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Updates an existing Warranty model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->updated_at = date('Y-m-d H:i:s');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Warranty has been updated successfully.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        // Prepare dropdown data
        $products = ArrayHelper::map(Product::find()->where(['>', 'warranty_period', 0])->all(), 'id', 'name');
        $customers = ArrayHelper::map(Customer::find()->all(), 'id', 'name');
        $statuses = ArrayHelper::map(WarrantyStatus::find()->all(), 'id', 'name');
        
        return $this->render('update', [
            'model' => $model,
            'products' => $products,
            'customers' => $customers,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Deletes an existing Warranty model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        Yii::$app->session->setFlash('success', 'Warranty has been deleted successfully.');
        return $this->redirect(['index']);
    }

    /**
     * Get product information for AJAX request
     * @return string JSON formatted product data
     */
    public function actionGetProductInfo($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $product = Product::findOne($id);
        
        if (!$product) {
            return ['error' => 'Product not found'];
        }
        
        // Calculate warranty end date based on the product's warranty period
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime("+{$product->warranty_period} days"));
        
        return [
            'name' => $product->name,
            'warranty_period' => $product->warranty_period,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ];
    }

    /**
     * Get customer information for AJAX request
     * @return string JSON formatted customer data
     */
    public function actionGetCustomerInfo($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $customer = Customer::findOne($id);
        
        if (!$customer) {
            return ['error' => 'Customer not found'];
        }
        
        return [
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'address' => $customer->address,
        ];
    }

    /**
     * Display warranty report page
     * @return mixed
     */
    public function actionReport()
    {
        // Get warranty stats
        $totalWarranties = Warranty::find()->count();
        $activeWarranties = Warranty::find()->where(['active' => true])->count();
        
        // Warranties by status
        $statusStats = Yii::$app->db->createCommand("
            SELECT ws.name, ws.color, COUNT(w.id) as count
            FROM warranty w
            JOIN warranty_status ws ON w.status_id = ws.id
            GROUP BY w.status_id, ws.name, ws.color
            ORDER BY count DESC
        ")->queryAll();
        
        // Warranties expiring in the next 30 days
        $expiringWarranties = Warranty::find()
            ->where(['active' => true])
            ->andWhere(['between', 'end_date', date('Y-m-d'), date('Y-m-d', strtotime('+30 days'))])
            ->count();
            
        // Most common products under warranty
        $productStats = Yii::$app->db->createCommand("
            SELECT p.name, COUNT(w.id) as count
            FROM warranty w
            JOIN product p ON w.product_id = p.id
            WHERE w.active = true
            GROUP BY w.product_id, p.name
            ORDER BY count DESC
            LIMIT 10
        ")->queryAll();
        
        return $this->render('report', [
            'totalWarranties' => $totalWarranties,
            'activeWarranties' => $activeWarranties,
            'expiringWarranties' => $expiringWarranties,
            'statusStats' => $statusStats,
            'productStats' => $productStats,
        ]);
    }

    /**
     * Print warranty details
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionPrint($id)
    {
        $model = $this->findModel($id);
        
        // Get warranty details
        $warrantyDetails = WarrantyDetail::find()
            ->where(['warranty_id' => $id])
            ->orderBy(['service_date' => SORT_DESC])
            ->all();
            
        // Set layout to print layout (no header/footer)
        $this->layout = 'print';
        
        return $this->render('print', [
            'model' => $model,
            'warrantyDetails' => $warrantyDetails,
        ]);
    }

    /**
     * Finds the Warranty model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Warranty the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Warranty::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested warranty does not exist.');
    }
}