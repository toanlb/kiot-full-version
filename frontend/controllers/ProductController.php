<?php
namespace frontend\controllers;

use Yii;
use common\models\Product;
use common\models\ProductSearch;
use common\models\ProductCategory;
use common\models\Stock;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\helpers\Json;

/**
 * ProductController implements the CRUD actions for Product model in POS frontend.
 */
class ProductController extends Controller
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
                        'actions' => ['index', 'view', 'list', 'search', 'by-category', 'get-details', 'get-stock'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'search' => ['POST', 'GET'],
                    'get-details' => ['POST', 'GET'],
                    'get-stock' => ['POST', 'GET'],
                ],
            ],
        ];
    }

    /**
     * Lists all products for POS view.
     * @return mixed
     */
    public function actionIndex()
    {
        // Get active categories
        $categories = ProductCategory::find()
            ->where(['status' => 1])
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->all();

        // Get featured products for homepage
        $featuredProducts = Product::find()
            ->where(['status' => 1])
            ->orderBy(['updated_at' => SORT_DESC])
            ->limit(12)
            ->all();

        return $this->render('index', [
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
        ]);
    }

    /**
     * Shows product listing by category
     * @param integer $id Category ID
     * @return mixed
     */
    public function actionByCategory($id)
    {
        $category = ProductCategory::findOne($id);
        
        if (!$category) {
            throw new NotFoundHttpException('Danh mục không tồn tại.');
        }
        
        // Get products in this category
        $products = Product::find()
            ->where(['category_id' => $id, 'status' => 1])
            ->orderBy(['name' => SORT_ASC])
            ->all();
        
        // Get all categories for sidebar
        $categories = ProductCategory::find()
            ->where(['status' => 1])
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->all();
        
        return $this->render('by-category', [
            'category' => $category,
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    /**
     * Search products via AJAX for POS
     * @return array JSON response
     */
    public function actionSearch()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $term = Yii::$app->request->get('term', '');
        $warehouseId = Yii::$app->request->get('warehouse_id', null);
        
        $query = Product::find()
            ->select(['product.id', 'product.code', 'product.name', 'product.barcode', 'product.selling_price', 'product.is_combo', 'product_unit.name as unit_name'])
            ->joinWith('unit')
            ->where(['product.status' => 1]);
        
        // Apply search term
        if (!empty($term)) {
            $query->andWhere(['or',
                ['like', 'product.code', $term],
                ['like', 'product.name', $term],
                ['like', 'product.barcode', $term],
            ]);
        }
        
        // Apply warehouse filter for stock
        if ($warehouseId) {
            $query->joinWith(['stocks' => function ($q) use ($warehouseId) {
                $q->andWhere(['warehouse_id' => $warehouseId]);
                $q->andWhere(['>', 'quantity', 0]);
            }]);
        }
        
        $products = $query->limit(30)->asArray()->all();
        
        $result = [];
        foreach ($products as $product) {
            // Get stock information
            $stock = 0;
            if ($warehouseId) {
                $stockModel = Stock::findOne(['product_id' => $product['id'], 'warehouse_id' => $warehouseId]);
                $stock = $stockModel ? $stockModel->quantity : 0;
            } else {
                $stock = Stock::find()
                    ->where(['product_id' => $product['id']])
                    ->sum('quantity') ?: 0;
            }
            
            // Get main image URL
            $mainImage = \common\models\ProductImage::find()
                ->where(['product_id' => $product['id'], 'is_main' => 1])
                ->one();
            
            $imageUrl = $mainImage ? Yii::$app->urlManager->createAbsoluteUrl('/' . $mainImage->image) : Yii::$app->urlManager->createAbsoluteUrl('/img/no-image.png');
            
            $result[] = [
                'id' => $product['id'],
                'code' => $product['code'],
                'name' => $product['name'],
                'barcode' => $product['barcode'],
                'price' => $product['selling_price'],
                'price_formatted' => Yii::$app->formatter->asCurrency($product['selling_price']),
                'unit' => $product['unit_name'],
                'stock' => $stock,
                'is_combo' => $product['is_combo'],
                'image' => $imageUrl,
            ];
        }
        
        return ['results' => $result];
    }

    /**
     * Get detailed product information for POS via AJAX
     * @return array JSON response
     */
    public function actionGetDetails()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $id = Yii::$app->request->get('id');
        $warehouseId = Yii::$app->request->get('warehouse_id', null);
        
        if (!$id) {
            return [
                'success' => false, 
                'message' => 'Thiếu thông tin sản phẩm.'
            ];
        }
        
        $product = Product::findOne($id);
        
        if (!$product) {
            return [
                'success' => false, 
                'message' => 'Không tìm thấy sản phẩm.'
            ];
        }
        
        // Get stock information
        $stock = 0;
        if ($warehouseId) {
            $stockModel = Stock::findOne(['product_id' => $id, 'warehouse_id' => $warehouseId]);
            $stock = $stockModel ? $stockModel->quantity : 0;
        } else {
            $stock = Stock::find()
                ->where(['product_id' => $id])
                ->sum('quantity') ?: 0;
        }
        
        // Get images
        $images = [];
        foreach ($product->productImages as $image) {
            $images[] = [
                'id' => $image->id,
                'url' => Yii::$app->urlManager->createAbsoluteUrl('/' . $image->image),
                'is_main' => $image->is_main
            ];
        }
        
        // Get combo items if this is a combo product
        $comboItems = [];
        if ($product->is_combo) {
            foreach ($product->comboItems as $item) {
                $comboItems[] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_code' => $item->product->code,
                    'quantity' => $item->quantity,
                    'unit_name' => $item->unit->name,
                    'price' => $item->product->selling_price,
                    'price_formatted' => Yii::$app->formatter->asCurrency($item->product->selling_price),
                ];
            }
        }
        
        // Get units with conversion factors
        $units = [];
        $units[] = [
            'id' => $product->unit_id,
            'name' => $product->unit->name,
            'abbreviation' => $product->unit->abbreviation,
            'is_default' => true,
            'conversion_factor' => 1
        ];
        
        $conversions = $product->productUnitConversions;
        foreach ($conversions as $conversion) {
            if ($conversion->from_unit_id == $product->unit_id) {
                $units[] = [
                    'id' => $conversion->to_unit_id,
                    'name' => $conversion->toUnit->name,
                    'abbreviation' => $conversion->toUnit->abbreviation,
                    'is_default' => false,
                    'conversion_factor' => $conversion->conversion_factor
                ];
            }
        }
        
        // Build result
        $result = [
            'success' => true,
            'product' => [
                'id' => $product->id,
                'code' => $product->code,
                'barcode' => $product->barcode,
                'name' => $product->name,
                'category_id' => $product->category_id,
                'category_name' => $product->category ? $product->category->name : null,
                'description' => $product->description,
                'short_description' => $product->short_description,
                'cost_price' => $product->cost_price,
                'selling_price' => $product->selling_price,
                'selling_price_formatted' => Yii::$app->formatter->asCurrency($product->selling_price),
                'unit_id' => $product->unit_id,
                'unit_name' => $product->unit->name,
                'is_combo' => $product->is_combo,
                'stock' => $stock,
                'warranty_period' => $product->warranty_period,
                'images' => $images,
                'combo_items' => $comboItems,
                'units' => $units
            ]
        ];
        
        return $result;
    }
    
    /**
     * Get product stock information via AJAX
     * @return array JSON response
     */
    public function actionGetStock()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $id = Yii::$app->request->get('id');
        $warehouseId = Yii::$app->request->get('warehouse_id', null);
        
        if (!$id) {
            return [
                'success' => false, 
                'message' => 'Thiếu thông tin sản phẩm.'
            ];
        }
        
        $query = Stock::find()->where(['product_id' => $id]);
        
        if ($warehouseId) {
            $query->andWhere(['warehouse_id' => $warehouseId]);
        }
        
        $stocks = $query->all();
        
        $result = [
            'success' => true,
            'product_id' => $id,
            'total_stock' => 0,
            'warehouses' => []
        ];
        
        foreach ($stocks as $stock) {
            $result['total_stock'] += $stock->quantity;
            $result['warehouses'][] = [
                'warehouse_id' => $stock->warehouse_id,
                'warehouse_name' => $stock->warehouse->name,
                'quantity' => $stock->quantity
            ];
        }
        
        return $result;
    }
    
    /**
     * Lists products with pagination for browsing
     * @return mixed
     */
    public function actionList()
    {
        $searchModel = new ProductSearch();
        $searchModel->status = 1; // Only active products
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'categories' => ArrayHelper::map(ProductCategory::find()->where(['status' => 1])->all(), 'id', 'name'),
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
        $model = $this->findModel($id);
        
        // Get related products (same category)
        $relatedProducts = Product::find()
            ->where(['category_id' => $model->category_id, 'status' => 1])
            ->andWhere(['!=', 'id', $model->id])
            ->limit(6)
            ->all();
        
        return $this->render('view', [
            'model' => $model,
            'relatedProducts' => $relatedProducts,
        ]);
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