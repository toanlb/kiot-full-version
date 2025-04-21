<?php
namespace backend\controllers;

use Yii;
use common\models\ProductAttribute;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\components\AccessControl; 
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * ProductAttributeController implements the CRUD actions for ProductAttribute model.
 */
class ProductAttributeController extends Controller
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
     * Lists all ProductAttribute models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ProductAttribute::find(),
            'sort' => [
                'defaultOrder' => [
                    'sort_order' => SORT_ASC,
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
     * Displays a single ProductAttribute model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        // Get products with this attribute
        $attributeValuesProvider = new ActiveDataProvider([
            'query' => $model->getProductAttributeValues(),
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        
        return $this->render('view', [
            'model' => $model,
            'attributeValuesProvider' => $attributeValuesProvider,
        ]);
    }

    /**
     * Creates a new ProductAttribute model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ProductAttribute();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            // Set timestamps
            $model->created_at = date('Y-m-d H:i:s');
            $model->updated_at = date('Y-m-d H:i:s');
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Thuộc tính đã được tạo thành công.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ProductAttribute model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            // Update timestamp
            $model->updated_at = date('Y-m-d H:i:s');
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Thuộc tính đã được cập nhật thành công.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ProductAttribute model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        // Check if attribute is being used
        $attributeValueCount = $model->getProductAttributeValues()->count();
        if ($attributeValueCount > 0) {
            Yii::$app->session->setFlash('error', "Không thể xóa thuộc tính này vì đang được sử dụng bởi {$attributeValueCount} sản phẩm.");
            return $this->redirect(['index']);
        }
        
        $model->delete();
        Yii::$app->session->setFlash('success', 'Thuộc tính đã được xóa thành công.');

        return $this->redirect(['index']);
    }

    /**
     * Validate model via AJAX
     * 
     * @return array
     */
    public function actionValidate()
    {
        $model = new ProductAttribute();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
    }

    /**
     * Finds the ProductAttribute model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ProductAttribute the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProductAttribute::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Không tìm thấy thuộc tính sản phẩm.');
    }
}