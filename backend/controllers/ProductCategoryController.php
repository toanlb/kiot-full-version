<?php
namespace backend\controllers;

use Yii;
use common\models\ProductCategory;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\components\AccessControl; 
use yii\web\UploadedFile;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * ProductCategoryController implements the CRUD actions for ProductCategory model.
 */
class ProductCategoryController extends Controller
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
     * Lists all ProductCategory models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ProductCategory::find(),
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
     * Displays a single ProductCategory model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        // Get products in this category
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
     * Creates a new ProductCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ProductCategory();
        $model->status = 1; // Default active

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            // Handle image upload
            $imageFile = UploadedFile::getInstance($model, 'imageFile');
            if ($imageFile) {
                $fileName = 'category_' . time() . '_' . uniqid() . '.' . $imageFile->extension;
                $filePath = Yii::getAlias('@backend/web/uploads/categories/') . $fileName;
                
                if ($imageFile->saveAs($filePath)) {
                    $model->image = 'uploads/categories/' . $fileName;
                }
            }
            
            // Set created/updated info
            $model->created_at = date('Y-m-d H:i:s');
            $model->updated_at = date('Y-m-d H:i:s');
            $model->created_by = Yii::$app->user->id;
            $model->updated_by = Yii::$app->user->id;
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Danh mục đã được tạo thành công.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'categories' => \yii\helpers\ArrayHelper::map(
                ProductCategory::find()->all(),
                'id',
                'name'
            ),
        ]);
    }

    /**
     * Updates an existing ProductCategory model.
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

        $oldImage = $model->image;
        
        if ($model->load(Yii::$app->request->post())) {
            // Handle image upload
            $imageFile = UploadedFile::getInstance($model, 'imageFile');
            if ($imageFile) {
                $fileName = 'category_' . time() . '_' . uniqid() . '.' . $imageFile->extension;
                $filePath = Yii::getAlias('@backend/web/uploads/categories/') . $fileName;
                
                if ($imageFile->saveAs($filePath)) {
                    $model->image = 'uploads/categories/' . $fileName;
                    
                    // Delete old image if exists
                    if ($oldImage && file_exists(Yii::getAlias('@backend/web/') . $oldImage)) {
                        unlink(Yii::getAlias('@backend/web/') . $oldImage);
                    }
                }
            } else {
                $model->image = $oldImage; // Keep old image if no new one uploaded
            }
            
            // Set updated info
            $model->updated_at = date('Y-m-d H:i:s');
            $model->updated_by = Yii::$app->user->id;
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Danh mục đã được cập nhật thành công.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'categories' => \yii\helpers\ArrayHelper::map(
                ProductCategory::find()->where(['!=', 'id', $model->id])->all(),
                'id',
                'name'
            ),
        ]);
    }

    /**
     * Deletes an existing ProductCategory model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        // Check if category has children
        $hasChildren = ProductCategory::find()->where(['parent_id' => $id])->exists();
        if ($hasChildren) {
            Yii::$app->session->setFlash('error', 'Không thể xóa danh mục này vì có danh mục con.');
            return $this->redirect(['index']);
        }
        
        // Check if category has products
        $hasProducts = $model->getProducts()->exists();
        if ($hasProducts) {
            Yii::$app->session->setFlash('error', 'Không thể xóa danh mục này vì có sản phẩm liên quan.');
            return $this->redirect(['index']);
        }
        
        // Delete image if exists
        if ($model->image && file_exists(Yii::getAlias('@backend/web/') . $model->image)) {
            unlink(Yii::getAlias('@backend/web/') . $model->image);
        }
        
        $model->delete();
        Yii::$app->session->setFlash('success', 'Danh mục đã được xóa thành công.');

        return $this->redirect(['index']);
    }

    /**
     * Validate model via AJAX
     * 
     * @return array
     */
    public function actionValidate()
    {
        $model = new ProductCategory();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
    }

    /**
     * Finds the ProductCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ProductCategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProductCategory::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Không tìm thấy danh mục sản phẩm.');
    }
}