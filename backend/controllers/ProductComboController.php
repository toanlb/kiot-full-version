<?php
namespace backend\controllers;

use Yii;
use common\models\Product;
use common\models\ProductCombo;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\components\AccessControl; 
use yii\web\Response;
use yii\helpers\ArrayHelper;

/**
 * ProductComboController implements the actions for managing product combos.
 */
class ProductComboController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'get-products', 'calculate-price'],
                        'allow' => true,
                        'roles' => ['@'], // Authenticated users
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'calculate-price' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all product combos.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Product::find()->where(['is_combo' => 1]),
            'sort' => [
                'defaultOrder' => [
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
     * Displays a single combo product.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findComboProduct($id);
        
        // Get combo items
        $comboItemsProvider = new ActiveDataProvider([
            'query' => ProductCombo::find()->where(['combo_id' => $id]),
            'pagination' => false,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_ASC,
                ]
            ],
        ]);
        
        return $this->render('view', [
            'model' => $model,
            'comboItemsProvider' => $comboItemsProvider,
        ]);
    }

    /**
     * Creates a new combo product.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Product();
        $model->is_combo = 1; // This is a combo product
        $model->status = 1; // Default active
        
        // Pre-load combo items array
        $comboItems = [];

        if ($model->load(Yii::$app->request->post())) {
            // Set timestamps
            $model->created_at = date('Y-m-d H:i:s');
            $model->updated_at = date('Y-m-d H:i:s');
            $model->created_by = Yii::$app->user->id;
            $model->updated_by = Yii::$app->user->id;
            
            // Start a transaction
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    // Process combo items
                    $itemsData = Yii::$app->request->post('ComboItems', []);
                    
                    foreach ($itemsData as $item) {
                        if (!empty($item['product_id']) && !empty($item['quantity']) && !empty($item['unit_id'])) {
                            $comboItem = new ProductCombo();
                            $comboItem->combo_id = $model->id;
                            $comboItem->product_id = $item['product_id'];
                            $comboItem->quantity = $item['quantity'];
                            $comboItem->unit_id = $item['unit_id'];
                            $comboItem->created_at = date('Y-m-d H:i:s');
                            $comboItem->updated_at = date('Y-m-d H:i:s');
                            
                            if (!$comboItem->save()) {
                                throw new \Exception('Không thể lưu thành phần combo.');
                            }
                        }
                    }
                    
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Sản phẩm combo đã được tạo thành công.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $model,
            'comboItems' => $comboItems,
            'categories' => ArrayHelper::map(
                \common\models\ProductCategory::find()->all(),
                'id',
                'name'
            ),
            'units' => ArrayHelper::map(
                \common\models\ProductUnit::find()->all(),
                'id',
                'name'
            ),
        ]);
    }

    /**
     * Updates an existing combo product.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findComboProduct($id);
        
        // Load existing combo items
        $comboItems = ProductCombo::find()->where(['combo_id' => $id])->all();

        if ($model->load(Yii::$app->request->post())) {
            // Set timestamps
            $model->updated_at = date('Y-m-d H:i:s');
            $model->updated_by = Yii::$app->user->id;
            
            // Make sure it's still marked as combo
            $model->is_combo = 1;
            
            // Start a transaction
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    // Delete all existing combo items
                    ProductCombo::deleteAll(['combo_id' => $model->id]);
                    
                    // Process new combo items
                    $itemsData = Yii::$app->request->post('ComboItems', []);
                    
                    foreach ($itemsData as $item) {
                        if (!empty($item['product_id']) && !empty($item['quantity']) && !empty($item['unit_id'])) {
                            $comboItem = new ProductCombo();
                            $comboItem->combo_id = $model->id;
                            $comboItem->product_id = $item['product_id'];
                            $comboItem->quantity = $item['quantity'];
                            $comboItem->unit_id = $item['unit_id'];
                            $comboItem->created_at = date('Y-m-d H:i:s');
                            $comboItem->updated_at = date('Y-m-d H:i:s');
                            
                            if (!$comboItem->save()) {
                                throw new \Exception('Không thể lưu thành phần combo.');
                            }
                        }
                    }
                    
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Sản phẩm combo đã được cập nhật thành công.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
            'comboItems' => $comboItems,
            'categories' => ArrayHelper::map(
                \common\models\ProductCategory::find()->all(),
                'id',
                'name'
            ),
            'units' => ArrayHelper::map(
                \common\models\ProductUnit::find()->all(),
                'id',
                'name'
            ),
        ]);
    }

    /**
     * Deletes a combo product.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findComboProduct($id);
        
        // Start a transaction
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Delete all combo items first
            ProductCombo::deleteAll(['combo_id' => $id]);
            
            // Then delete the combo product
            $model->delete();
            
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Sản phẩm combo đã được xóa thành công.');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Đã xảy ra lỗi khi xóa: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Get available products for combo via AJAX
     * @return mixed
     */
    public function actionGetProducts()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $term = Yii::$app->request->get('term', '');
        $excludeId = Yii::$app->request->get('exclude_id', 0);
        
        $query = Product::find()
            ->where(['status' => 1])
            ->andWhere(['!=', 'id', $excludeId]) // Exclude the combo product itself
            ->andWhere(['is_combo' => 0]); // Exclude other combos
        
        if (!empty($term)) {
            $query->andWhere(['or',
                ['like', 'code', $term],
                ['like', 'name', $term],
                ['like', 'barcode', $term],
            ]);
        }
        
        $products = $query->limit(20)->all();
        
        $result = [];
        foreach ($products as $product) {
            $result[] = [
                'id' => $product->id,
                'text' => $product->name . ' (' . $product->code . ')',
                'code' => $product->code,
                'name' => $product->name,
                'price' => $product->selling_price,
                'unit_id' => $product->unit_id,
                'unit_name' => $product->unit->name,
            ];
        }
        
        return ['results' => $result];
    }

    /**
     * Calculate total price of combo items via AJAX
     * @return mixed
     */
    public function actionCalculatePrice()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $items = Yii::$app->request->post('items', []);
        $total = 0;
        
        if (!empty($items)) {
            foreach ($items as $item) {
                if (isset($item['product_id'], $item['quantity'])) {
                    $product = Product::findOne($item['product_id']);
                    if ($product) {
                        $total += $product->selling_price * $item['quantity'];
                    }
                }
            }
        }
        
        return [
            'success' => true,
            'total' => $total,
            'formatted' => Yii::$app->formatter->asCurrency($total),
        ];
    }

    /**
     * Finds a combo product model based on its primary key value.
     * @param integer $id
     * @return Product the loaded model
     * @throws NotFoundHttpException if the model cannot be found or is not a combo
     */
    protected function findComboProduct($id)
    {
        $model = Product::findOne($id);
        
        if ($model === null) {
            throw new NotFoundHttpException('Không tìm thấy sản phẩm combo.');
        }
        
        if ($model->is_combo !== 1) {
            throw new NotFoundHttpException('Sản phẩm này không phải là combo.');
        }
        
        return $model;
    }
}