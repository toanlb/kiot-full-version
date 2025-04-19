<?php
namespace backend\controllers;

use Yii;
use common\models\Product;
use common\models\ProductSearch;
use common\models\ProductImage;
use common\models\ProductCategory;
use common\models\ProductAttributeValue;
use common\models\ProductCombo;
use common\models\ProductPriceHistory;
use common\models\ProductUnit;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'bulk-delete', 'validate', 'upload-image', 'delete-image', 'import', 'export', 'get-categories', 'get-units'],
                        'allow' => true,
                        'roles' => ['@'], // Authenticated users
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'bulk-delete' => ['POST'],
                    'delete-image' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Product models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'categories' => ArrayHelper::map(ProductCategory::find()->all(), 'id', 'name'),
        ]);
    }

    /**
     * Displays a single Product model.
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
     * Creates a new Product model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Product();
        $model->status = 1; // Default active

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Handle images upload
            $this->handleImageUpload($model);
            
            // Handle attributes
            $this->handleAttributes($model);
            
            // Handle combo items if this is a combo product
            if ($model->is_combo) {
                $this->handleComboItems($model);
            }
            
            // Save price history
            $this->savePriceHistory($model);
            
            Yii::$app->session->setFlash('success', 'Sản phẩm đã được tạo thành công.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'categories' => ArrayHelper::map(ProductCategory::find()->all(), 'id', 'name'),
            'units' => ArrayHelper::map(ProductUnit::find()->all(), 'id', 'name'),
        ]);
    }

    /**
     * Updates an existing Product model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldPrices = [
            'cost_price' => $model->cost_price,
            'selling_price' => $model->selling_price,
        ];

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Handle images upload
            $this->handleImageUpload($model);
            
            // Handle attributes
            $this->handleAttributes($model);
            
            // Handle combo items if this is a combo product
            if ($model->is_combo) {
                $this->handleComboItems($model);
            }
            
            // Save price history if prices changed
            if ($oldPrices['cost_price'] != $model->cost_price || $oldPrices['selling_price'] != $model->selling_price) {
                $this->savePriceHistory($model);
            }
            
            Yii::$app->session->setFlash('success', 'Sản phẩm đã được cập nhật thành công.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'categories' => ArrayHelper::map(ProductCategory::find()->all(), 'id', 'name'),
            'units' => ArrayHelper::map(ProductUnit::find()->all(), 'id', 'name'),
        ]);
    }

    /**
     * Deletes an existing Product model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        try {
            $model->delete();
            Yii::$app->session->setFlash('success', 'Sản phẩm đã được xóa thành công.');
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Không thể xóa sản phẩm vì đã có dữ liệu liên quan.');
        }
        
        return $this->redirect(['index']);
    }
    
    /**
     * Bulk deletes selected products
     * @return mixed
     */
    public function actionBulkDelete()
    {
        $ids = Yii::$app->request->post('ids');
        if (!empty($ids)) {
            foreach ($ids as $id) {
                try {
                    $this->findModel($id)->delete();
                } catch (\Exception $e) {
                    // Log error but continue
                }
            }
            Yii::$app->session->setFlash('success', 'Các sản phẩm đã được xóa thành công.');
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Validates product form via AJAX
     * @return array JSON response
     */
    public function actionValidate()
    {
        $model = new Product();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
    }

    /**
     * Upload product image via AJAX
     * @return array JSON response
     */
    public function actionUploadImage()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $productId = Yii::$app->request->post('product_id');
        $model = $this->findModel($productId);
        
        $uploadedFile = UploadedFile::getInstanceByName('image');
        if ($uploadedFile) {
            $productImage = new ProductImage();
            $productImage->product_id = $model->id;
            $productImage->created_at = date('Y-m-d H:i:s');
            
            // Generate unique filename
            $fileName = 'product_' . $model->id . '_' . time() . '_' . uniqid() . '.' . $uploadedFile->extension;
            $filePath = Yii::getAlias('@backend/web/uploads/products/') . $fileName;
            
            if ($uploadedFile->saveAs($filePath)) {
                $productImage->image = 'uploads/products/' . $fileName;
                
                // If no main image exists, set this as main
                if (!ProductImage::findOne(['product_id' => $model->id, 'is_main' => 1])) {
                    $productImage->is_main = 1;
                } else {
                    $productImage->is_main = 0;
                }
                
                if ($productImage->save()) {
                    return [
                        'success' => true,
                        'id' => $productImage->id,
                        'url' => '/uploads/products/' . $fileName
                    ];
                }
            }
        }
        
        return ['success' => false, 'error' => 'Không thể tải lên hình ảnh'];
    }

    /**
     * Delete product image via AJAX
     * @return array JSON response
     */
    public function actionDeleteImage()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $id = Yii::$app->request->post('id');
        $image = ProductImage::findOne($id);
        
        if ($image && $image->delete()) {
            // If deleted image was main, set another image as main
            if ($image->is_main) {
                $newMainImage = ProductImage::findOne(['product_id' => $image->product_id]);
                if ($newMainImage) {
                    $newMainImage->is_main = 1;
                    $newMainImage->save();
                }
            }
            
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => 'Không thể xóa hình ảnh'];
    }

    /**
     * Import products from Excel file
     * @return mixed
     */
    public function actionImport()
    {
        // Implementation for importing products from Excel
        return $this->render('import');
    }

    /**
     * Export products to Excel file
     * @return mixed
     */
    public function actionExport()
    {
        // Implementation for exporting products to Excel
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = false; // No pagination for export
        
        // Generate Excel file
        // ...
        
        return $this->redirect(['index']);
    }

    /**
     * Get categories via AJAX
     * @return array JSON response
     */
    public function actionGetCategories()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $categories = ProductCategory::find()->all();
        $result = [];
        
        foreach ($categories as $category) {
            $result[] = [
                'id' => $category->id,
                'name' => $category->name,
                'parent_id' => $category->parent_id
            ];
        }
        
        return $result;
    }

    /**
     * Get units via AJAX
     * @return array JSON response
     */
    public function actionGetUnits()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $units = ProductUnit::find()->all();
        $result = [];
        
        foreach ($units as $unit) {
            $result[] = [
                'id' => $unit->id,
                'name' => $unit->name,
                'abbreviation' => $unit->abbreviation
            ];
        }
        
        return $result;
    }

    /**
     * Handles image upload for product
     * @param Product $model
     */
    protected function handleImageUpload($model)
    {
        $uploadedImages = UploadedFile::getInstances($model, 'imageFiles');
        if ($uploadedImages) {
            foreach ($uploadedImages as $uploadedImage) {
                $productImage = new ProductImage();
                $productImage->product_id = $model->id;
                $productImage->created_at = date('Y-m-d H:i:s');
                
                // Generate unique filename
                $fileName = 'product_' . $model->id . '_' . time() . '_' . uniqid() . '.' . $uploadedImage->extension;
                $filePath = Yii::getAlias('@backend/web/uploads/products/') . $fileName;
                
                if ($uploadedImage->saveAs($filePath)) {
                    $productImage->image = 'uploads/products/' . $fileName;
                    
                    // If no main image exists, set this as main
                    if (!ProductImage::findOne(['product_id' => $model->id, 'is_main' => 1])) {
                        $productImage->is_main = 1;
                    }
                    
                    $productImage->save();
                }
            }
        }
        
        // Set main image if specified
        $mainImageId = Yii::$app->request->post('main_image_id');
        if ($mainImageId) {
            // Reset all images to non-main
            ProductImage::updateAll(['is_main' => 0], ['product_id' => $model->id]);
            // Set new main image
            ProductImage::updateAll(['is_main' => 1], ['id' => $mainImageId]);
        }
    }
    
    /**
     * Handles attribute values for product
     * @param Product $model
     */
    protected function handleAttributes($model)
    {
        $attributeValues = Yii::$app->request->post('ProductAttributeValue', []);
        
        // Delete old attribute values
        ProductAttributeValue::deleteAll(['product_id' => $model->id]);
        
        // Save new attribute values
        foreach ($attributeValues as $attributeId => $value) {
            if (!empty($value)) {
                $attrValue = new ProductAttributeValue();
                $attrValue->product_id = $model->id;
                $attrValue->attribute_id = $attributeId;
                $attrValue->value = $value;
                $attrValue->created_at = date('Y-m-d H:i:s');
                $attrValue->updated_at = date('Y-m-d H:i:s');
                $attrValue->save();
            }
        }
    }
    
    /**
     * Handles combo items for combo product
     * @param Product $model
     */
    protected function handleComboItems($model)
    {
        $comboItems = Yii::$app->request->post('ComboItems', []);
        
        // Delete old combo items
        ProductCombo::deleteAll(['combo_id' => $model->id]);
        
        // Save new combo items
        foreach ($comboItems as $item) {
            if (!empty($item['product_id']) && !empty($item['quantity'])) {
                $comboItem = new ProductCombo();
                $comboItem->combo_id = $model->id;
                $comboItem->product_id = $item['product_id'];
                $comboItem->quantity = $item['quantity'];
                $comboItem->unit_id = $item['unit_id'];
                $comboItem->created_at = date('Y-m-d H:i:s');
                $comboItem->updated_at = date('Y-m-d H:i:s');
                $comboItem->save();
            }
        }
    }
    
    /**
     * Saves price history for product
     * @param Product $model
     */
    protected function savePriceHistory($model)
    {
        $priceHistory = new ProductPriceHistory();
        $priceHistory->product_id = $model->id;
        $priceHistory->cost_price = $model->cost_price;
        $priceHistory->selling_price = $model->selling_price;
        $priceHistory->effective_date = date('Y-m-d H:i:s');
        $priceHistory->created_at = date('Y-m-d H:i:s');
        $priceHistory->created_by = Yii::$app->user->id;
        $priceHistory->note = 'Cập nhật giá';
        $priceHistory->save();
    }

    /**
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Product the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Product::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Không tìm thấy sản phẩm.');
    }
}