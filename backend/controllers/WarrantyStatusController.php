<?php

namespace backend\controllers;

use Yii;
use common\models\WarrantyStatus;
use common\models\WarrantyStatusSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\components\AccessControl;

/**
 * WarrantyStatusController implements the CRUD actions for WarrantyStatus model.
 */
class WarrantyStatusController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete'],
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
     * Lists all WarrantyStatus models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new WarrantyStatusSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single WarrantyStatus model.
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
     * Creates a new WarrantyStatus model.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new WarrantyStatus();

        // Set default values
        if (empty($model->color)) {
            $model->color = '#3c8dbc'; // Default blue color
        }
        
        // Get the maximum sort order and add 1
        $maxSortOrder = WarrantyStatus::find()->max('sort_order');
        $model->sort_order = $maxSortOrder ? $maxSortOrder + 1 : 1;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Warranty status has been created successfully.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing WarrantyStatus model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Warranty status has been updated successfully.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing WarrantyStatus model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        // Check if status is being used in warranties
        $count = Yii::$app->db->createCommand("
            SELECT COUNT(*) FROM warranty WHERE status_id = :status_id
        ", [':status_id' => $id])->queryScalar();
        
        if ($count > 0) {
            Yii::$app->session->setFlash('error', 'This status cannot be deleted because it is being used in ' . $count . ' warranties.');
            return $this->redirect(['index']);
        }
        
        // Also check in warranty details
        $count = Yii::$app->db->createCommand("
            SELECT COUNT(*) FROM warranty_detail WHERE status_id = :status_id
        ", [':status_id' => $id])->queryScalar();
        
        if ($count > 0) {
            Yii::$app->session->setFlash('error', 'This status cannot be deleted because it is being used in ' . $count . ' warranty details.');
            return $this->redirect(['index']);
        }
        
        $this->findModel($id)->delete();

        Yii::$app->session->setFlash('success', 'Warranty status has been deleted successfully.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the WarrantyStatus model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return WarrantyStatus the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = WarrantyStatus::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested warranty status does not exist.');
    }
}