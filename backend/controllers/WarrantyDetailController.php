<?php

namespace backend\controllers;

use Yii;
use common\models\WarrantyDetail;
use common\models\Warranty;
use common\models\WarrantyStatus;
use common\models\Product;
use common\models\WarrantyDetailSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\components\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * WarrantyDetailController implements the CRUD actions for WarrantyDetail model.
 */
class WarrantyDetailController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'print'],
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
     * Lists all WarrantyDetail models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new WarrantyDetailSearch();
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
     * Displays a single WarrantyDetail model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new WarrantyDetail model.
     * @param integer $warranty_id Warranty ID
     * @return mixed
     */
    public function actionCreate($warranty_id = null)
    {
        $model = new WarrantyDetail();
        $model->service_date = date('Y-m-d H:i:s');
        $model->handled_by = Yii::$app->user->id;
        $model->created_at = date('Y-m-d H:i:s');
        
        // Set warranty_id if provided
        if ($warranty_id) {
            $model->warranty_id = $warranty_id;
            $warranty = Warranty::findOne($warranty_id);
            if (!$warranty) {
                throw new NotFoundHttpException('The requested warranty does not exist.');
            }
        }

        if ($model->load(Yii::$app->request->post())) {
            // Calculate total cost if not provided
            if (empty($model->total_cost)) {
                $model->total_cost = (float)$model->replacement_cost + (float)$model->service_cost;
            }
            
            if ($model->save()) {
                // Update warranty status
                if ($model->warranty_id) {
                    $warranty = Warranty::findOne($model->warranty_id);
                    if ($warranty) {
                        $warranty->status_id = $model->status_id;
                        $warranty->save();
                    }
                }
                
                Yii::$app->session->setFlash('success', 'Warranty detail has been created successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        // Prepare dropdown data
        $warranties = ArrayHelper::map(Warranty::find()->where(['active' => true])->all(), 'id', function($model) {
            return $model->code . ' - ' . $model->product->name . ' (' . $model->customer->name . ')';
        });
        $statuses = ArrayHelper::map(WarrantyStatus::find()->all(), 'id', 'name');
        $replacementProducts = ArrayHelper::map(Product::find()->all(), 'id', 'name');
        
        return $this->render('create', [
            'model' => $model,
            'warranties' => $warranties,
            'statuses' => $statuses,
            'replacementProducts' => $replacementProducts,
        ]);
    }

    /**
     * Updates an existing WarrantyDetail model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            // Calculate total cost if not provided
            if (empty($model->total_cost)) {
                $model->total_cost = (float)$model->replacement_cost + (float)$model->service_cost;
            }
            
            if ($model->save()) {
                // Update warranty status
                if ($model->warranty_id) {
                    $warranty = Warranty::findOne($model->warranty_id);
                    if ($warranty) {
                        $warranty->status_id = $model->status_id;
                        $warranty->save();
                    }
                }
                
                Yii::$app->session->setFlash('success', 'Warranty detail has been updated successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        // Prepare dropdown data
        $warranties = ArrayHelper::map(Warranty::find()->all(), 'id', function($model) {
            return $model->code . ' - ' . $model->product->name . ' (' . $model->customer->name . ')';
        });
        $statuses = ArrayHelper::map(WarrantyStatus::find()->all(), 'id', 'name');
        $replacementProducts = ArrayHelper::map(Product::find()->all(), 'id', 'name');
        
        return $this->render('update', [
            'model' => $model,
            'warranties' => $warranties,
            'statuses' => $statuses,
            'replacementProducts' => $replacementProducts,
        ]);
    }

    /**
     * Deletes an existing WarrantyDetail model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $warranty_id = $model->warranty_id;
        $model->delete();

        Yii::$app->session->setFlash('success', 'Warranty detail has been deleted successfully.');
        
        // If we have warranty_id, redirect to warranty view page
        if ($warranty_id) {
            return $this->redirect(['warranty/view', 'id' => $warranty_id]);
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Print warranty detail
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionPrint($id)
    {
        $model = $this->findModel($id);
        
        // Set layout to print layout (no header/footer)
        $this->layout = 'print';
        
        return $this->render('print', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the WarrantyDetail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return WarrantyDetail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = WarrantyDetail::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested warranty detail does not exist.');
    }
}