<?php
namespace backend\controllers;

use Yii;
use common\models\ProductUnit;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\components\AccessControl; 
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * ProductUnitController implements the CRUD actions for ProductUnit model.
 */
class ProductUnitController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'validate'],
                        'allow' => true,
                        'roles' => ['@'], // Authenticated users
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
     * Lists all ProductUnit models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ProductUnit::find(),
            'sort' => [
                'defaultOrder' => [
                    'is_default' => SORT_DESC,
                    'name' => SORT_ASC,
                ]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ProductUnit model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        // Get products using this unit
        $productsProvider = new ActiveDataProvider([
            'query' => $model->getProducts(),
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        
        return $this->render('view', [
            'model' => $model,
            'productsProvider' => $productsProvider,
        ]);
    }

    /**
     * Creates a new ProductUnit model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ProductUnit();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            // If this is set as default, unset all other defaults
            if ($model->is_default) {
                ProductUnit::updateAll(['is_default' => 0]);
            }
            
            // Set timestamps
            $model->created_at = date('Y-m-d H:i:s');
            $model->updated_at = date('Y-m-d H:i:s');
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Đơn vị tính đã được tạo thành công.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ProductUnit model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $wasDefault = $model->is_default;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            // If this is set as default and wasn't before, unset all other defaults
            if ($model->is_default && !$wasDefault) {
                ProductUnit::updateAll(['is_default' => 0]);
            }
            
            // If this was default and is being unset, make sure at least one default exists
            if ($wasDefault && !$model->is_default) {
                $defaultCount = ProductUnit::find()->where(['is_default' => 1])->count();
                if ($defaultCount == 0) {
                    // Find first unit and make it default
                    $firstUnit = ProductUnit::find()->orderBy(['id' => SORT_ASC])->one();
                    if ($firstUnit && $firstUnit->id != $model->id) {
                        $firstUnit->is_default = 1;
                        $firstUnit->save();
                    } else {
                        // If this is the only unit, force it to be default
                        $model->is_default = 1;
                        Yii::$app->session->setFlash('warning', 'Phải có ít nhất một đơn vị tính mặc định trong hệ thống.');
                    }
                }
            }
            
            // Update timestamp
            $model->updated_at = date('Y-m-d H:i:s');
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Đơn vị tính đã được cập nhật thành công.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ProductUnit model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        // Check if unit is being used by products
        $productsCount = $model->getProducts()->count();
        if ($productsCount > 0) {
            Yii::$app->session->setFlash('error', "Không thể xóa đơn vị tính này vì đang được sử dụng bởi {$productsCount} sản phẩm.");
            return $this->redirect(['index']);
        }
        
        // Check if this is default unit
        if ($model->is_default) {
            // Find another unit to make default
            $anotherUnit = ProductUnit::find()->where(['!=', 'id', $model->id])->one();
            if ($anotherUnit) {
                $anotherUnit->is_default = 1;
                $anotherUnit->save();
            } else {
                Yii::$app->session->setFlash('error', "Không thể xóa đơn vị tính mặc định duy nhất trong hệ thống.");
                return $this->redirect(['index']);
            }
        }
        
        $model->delete();
        Yii::$app->session->setFlash('success', 'Đơn vị tính đã được xóa thành công.');

        return $this->redirect(['index']);
    }

    /**
     * Validate model via AJAX
     * 
     * @return array
     */
    public function actionValidate()
    {
        $model = new ProductUnit();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
    }

    /**
     * Finds the ProductUnit model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ProductUnit the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProductUnit::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Không tìm thấy đơn vị tính.');
    }
}