<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use common\models\Stock;
use common\models\Warehouse;
use common\models\Product;
use common\models\StockSearch;
use common\models\StockMovement;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\components\AccessControl;
use common\components\WarehouseFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\helpers\Json;

/**
 * InventoryController implements actions for inventory management.
 */
class InventoryController extends Controller
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
                        'actions' => ['index', 'view-stock'],
                        'permissions' => 'viewInventory',
                    ],
                    [
                        'actions' => ['warehouses', 'view-warehouse', 'create-warehouse', 'update-warehouse', 'delete-warehouse'],
                        'permissions' => 'manageWarehouses',
                    ],
                    [
                        'actions' => ['stock-movement', 'product-batch'],
                        'permissions' => ['viewInventory', 'createStockIn', 'createStockOut'],
                    ],
                    [
                        'actions' => ['adjust-stock'],
                        'permissions' => ['approveStockCheck', 'admin'],
                    ],
                ],
            ],
            'warehouseFilter' => [
                'class' => WarehouseFilter::className(),
                'only' => ['view-stock', 'view-warehouse', 'stock-movement', 'product-batch', 'adjust-stock'],
                'except' => ['index', 'warehouses', 'create-warehouse'],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete-warehouse' => ['POST'],
                    'adjust-stock' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Trang tổng quan kho hàng.
     * @return mixed
     */
    public function actionIndex()
    {
        // Lấy danh sách kho hàng dựa trên quyền người dùng
        if (Yii::$app->user->can('admin') || Yii::$app->user->can('storeManager')) {
            $warehouses = Warehouse::find()->where(['is_active' => 1])->all();
        } else {
            $userWarehouses = \common\models\UserWarehouse::find()
                ->select('warehouse_id')
                ->where(['user_id' => Yii::$app->user->id])
                ->column();
            
            $warehouses = Warehouse::find()
                ->where(['id' => $userWarehouses, 'is_active' => 1])
                ->all();
        }
        
        // Thống kê nhanh
        $totalProducts = Product::find()->count();
        $lowStockProducts = Stock::find()
            ->joinWith('product')
            ->where(['<', 'stock.quantity', new \yii\db\Expression('COALESCE(stock.min_stock, product.min_stock, 5)')])
            ->count();
        
        return $this->render('index', [
            'warehouses' => $warehouses,
            'totalProducts' => $totalProducts,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }

    /**
     * Xem tồn kho theo kho hàng
     * @param integer $warehouse_id
     * @return mixed
     */
    public function actionViewStock($warehouse_id = null)
    {
        $searchModel = new StockSearch();
        $params = Yii::$app->request->queryParams;
        
        if ($warehouse_id) {
            $params['StockSearch']['warehouse_id'] = $warehouse_id;
        }
        
        $dataProvider = $searchModel->search($params);
        
        // Lấy danh sách kho hàng dựa trên quyền người dùng
        if (Yii::$app->user->can('admin') || Yii::$app->user->can('storeManager')) {
            $warehouses = ArrayHelper::map(Warehouse::find()->where(['is_active' => 1])->all(), 'id', 'name');
        } else {
            $userWarehouses = \common\models\UserWarehouse::find()
                ->select('warehouse_id')
                ->where(['user_id' => Yii::$app->user->id])
                ->column();
            
            $warehouses = ArrayHelper::map(
                Warehouse::find()->where(['id' => $userWarehouses, 'is_active' => 1])->all(),
                'id',
                'name'
            );
        }
        
        return $this->render('view-stock', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'warehouse_id' => $warehouse_id,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Danh sách kho hàng
     * @return mixed
     */
    public function actionWarehouses()
    {
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => Warehouse::find(),
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_ASC,
                ]
            ],
        ]);
        
        return $this->render('warehouses', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Xem chi tiết kho hàng
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionViewWarehouse($id)
    {
        $model = $this->findWarehouseModel($id);
        
        // Thống kê nhanh kho này
        $totalStock = Stock::find()->where(['warehouse_id' => $id])->sum('quantity');
        $totalItems = Stock::find()->where(['warehouse_id' => $id])->count();
        $lowStockItems = Stock::find()
            ->joinWith('product')
            ->where(['warehouse_id' => $id])
            ->andWhere(['<', 'stock.quantity', new \yii\db\Expression('COALESCE(stock.min_stock, product.min_stock, 5)')])
            ->count();
        
        // Lấy các sản phẩm sắp hết
        $lowStockProducts = Stock::find()
            ->joinWith('product')
            ->where(['warehouse_id' => $id])
            ->andWhere(['<', 'stock.quantity', new \yii\db\Expression('COALESCE(stock.min_stock, product.min_stock, 5)')])
            ->limit(10)
            ->all();
        
        return $this->render('view-warehouse', [
            'model' => $model,
            'totalStock' => $totalStock,
            'totalItems' => $totalItems,
            'lowStockItems' => $lowStockItems,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }

    /**
     * Tạo kho hàng mới
     * @return mixed
     */
    public function actionCreateWarehouse()
    {
        $model = new Warehouse();
        $model->is_active = 1;
        $model->created_at = date('Y-m-d H:i:s');
        $model->updated_at = date('Y-m-d H:i:s');
        $model->created_by = Yii::$app->user->id;
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Kho hàng đã được tạo thành công.');
            return $this->redirect(['view-warehouse', 'id' => $model->id]);
        }
        
        // Lấy danh sách người quản lý
        $managers = ArrayHelper::map(
            \common\models\User::find()
                ->where(['status' => \common\models\User::STATUS_ACTIVE])
                ->all(),
            'id',
            'full_name'
        );
        
        return $this->render('create-warehouse', [
            'model' => $model,
            'managers' => $managers,
        ]);
    }

    /**
     * Cập nhật kho hàng
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdateWarehouse($id)
    {
        $model = $this->findWarehouseModel($id);
        $model->updated_at = date('Y-m-d H:i:s');
        $model->updated_by = Yii::$app->user->id;
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Kho hàng đã được cập nhật thành công.');
            return $this->redirect(['view-warehouse', 'id' => $model->id]);
        }
        
        // Lấy danh sách người quản lý
        $managers = ArrayHelper::map(
            \common\models\User::find()
                ->where(['status' => \common\models\User::STATUS_ACTIVE])
                ->all(),
            'id',
            'full_name'
        );
        
        return $this->render('update-warehouse', [
            'model' => $model,
            'managers' => $managers,
        ]);
    }

    /**
     * Xóa kho hàng
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDeleteWarehouse($id)
    {
        $model = $this->findWarehouseModel($id);
        
        // Kiểm tra xem kho có sản phẩm không
        $hasStock = Stock::find()->where(['warehouse_id' => $id])->exists();
        
        if ($hasStock) {
            Yii::$app->session->setFlash('error', 'Không thể xóa kho hàng đang còn sản phẩm.');
            return $this->redirect(['view-warehouse', 'id' => $id]);
        }
        
        // Kiểm tra các đơn nhập/xuất liên quan
        $hasStockIn = \common\models\StockIn::find()->where(['warehouse_id' => $id])->exists();
        $hasStockOut = \common\models\StockOut::find()->where(['warehouse_id' => $id])->exists();
        $hasTransferSource = \common\models\StockTransfer::find()->where(['source_warehouse_id' => $id])->exists();
        $hasTransferDest = \common\models\StockTransfer::find()->where(['destination_warehouse_id' => $id])->exists();
        
        if ($hasStockIn || $hasStockOut || $hasTransferSource || $hasTransferDest) {
            Yii::$app->session->setFlash('error', 'Không thể xóa kho hàng đã có dữ liệu liên quan.');
            return $this->redirect(['view-warehouse', 'id' => $id]);
        }
        
        $model->delete();
        
        Yii::$app->session->setFlash('success', 'Kho hàng đã được xóa thành công.');
        return $this->redirect(['warehouses']);
    }

    /**
     * Xem lịch sử chuyển động kho
     * @param integer $warehouse_id
     * @param integer $product_id
     * @return mixed
     */
    public function actionStockMovement($warehouse_id = null, $product_id = null)
    {
        $query = StockMovement::find()
            ->joinWith(['product', 'sourceWarehouse', 'destinationWarehouse', 'unit'])
            ->orderBy(['movement_date' => SORT_DESC]);
        
        if ($warehouse_id) {
            $query->andWhere(['or', 
                ['source_warehouse_id' => $warehouse_id], 
                ['destination_warehouse_id' => $warehouse_id]
            ]);
        }
        
        if ($product_id) {
            $query->andWhere(['stock_movement.product_id' => $product_id]);
        }
        
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        
        // Lấy danh sách kho hàng dựa trên quyền người dùng
        if (Yii::$app->user->can('admin') || Yii::$app->user->can('storeManager')) {
            $warehouses = ArrayHelper::map(Warehouse::find()->where(['is_active' => 1])->all(), 'id', 'name');
        } else {
            $userWarehouses = \common\models\UserWarehouse::find()
                ->select('warehouse_id')
                ->where(['user_id' => Yii::$app->user->id])
                                ->column();
            $warehouses = ArrayHelper::map(
                Warehouse::find()->where(['id' => $userWarehouses, 'is_active' => 1])->all(),
                'id',
                'name'
            );
        }
        
        return $this->render('stock-movement', [
            'dataProvider' => $dataProvider,
            'warehouses' => $warehouses,
            'warehouse_id' => $warehouse_id,
            'product_id' => $product_id,
        ]);
    }

    /**
     * Xem lô hàng theo sản phẩm và kho
     * @param integer $warehouse_id
     * @param integer $product_id
     * @return mixed
     */
    public function actionProductBatch($warehouse_id = null, $product_id = null)
    {
        $query = \common\models\ProductBatch::find()
            ->joinWith(['product', 'warehouse'])
            ->orderBy(['expiry_date' => SORT_ASC]);
        
        if ($warehouse_id) {
            $query->andWhere(['product_batch.warehouse_id' => $warehouse_id]);
        }
        
        if ($product_id) {
            $query->andWhere(['product_batch.product_id' => $product_id]);
        }
        
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        
        // Lấy danh sách kho hàng dựa trên quyền người dùng
        if (Yii::$app->user->can('admin') || Yii::$app->user->can('storeManager')) {
            $warehouses = ArrayHelper::map(Warehouse::find()->where(['is_active' => 1])->all(), 'id', 'name');
        } else {
            $userWarehouses = \common\models\UserWarehouse::find()
                ->select('warehouse_id')
                ->where(['user_id' => Yii::$app->user->id])
                ->column();
            
            $warehouses = ArrayHelper::map(
                Warehouse::find()->where(['id' => $userWarehouses, 'is_active' => 1])->all(),
                'id',
                'name'
            );
        }
        
        // Lấy danh sách sản phẩm
        $products = ArrayHelper::map(Product::find()->where(['status' => 1])->all(), 'id', 'name');
        
        return $this->render('product-batch', [
            'dataProvider' => $dataProvider,
            'warehouses' => $warehouses,
            'products' => $products,
            'warehouse_id' => $warehouse_id,
            'product_id' => $product_id,
        ]);
    }

    /**
     * Điều chỉnh số lượng tồn kho
     * @return mixed
     */
    public function actionAdjustStock()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $productId = Yii::$app->request->post('product_id');
        $warehouseId = Yii::$app->request->post('warehouse_id');
        $quantity = Yii::$app->request->post('quantity');
        $reason = Yii::$app->request->post('reason', '');
        
        if (!$productId || !$warehouseId || !isset($quantity)) {
            return [
                'success' => false,
                'message' => 'Thiếu thông tin cần thiết để điều chỉnh tồn kho.'
            ];
        }
        
        // Tìm stock hiện tại
        $stock = Stock::findOne(['product_id' => $productId, 'warehouse_id' => $warehouseId]);
        
        if (!$stock) {
            // Tạo mới nếu không tồn tại
            $stock = new Stock();
            $stock->product_id = $productId;
            $stock->warehouse_id = $warehouseId;
            $stock->quantity = 0;
            $stock->updated_at = date('Y-m-d H:i:s');
            $stock->save();
        }
        
        $oldQuantity = $stock->quantity;
        $difference = $quantity - $oldQuantity;
        
        if ($difference == 0) {
            return [
                'success' => true,
                'message' => 'Không có thay đổi số lượng tồn kho.'
            ];
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // Cập nhật số lượng tồn kho
            $stock->quantity = $quantity;
            $stock->updated_at = date('Y-m-d H:i:s');
            
            if (!$stock->save()) {
                throw new \Exception('Không thể cập nhật tồn kho.');
            }
            
            // Tạo bản ghi chuyển động kho
            $stockMovement = new StockMovement();
            $stockMovement->product_id = $productId;
            $stockMovement->quantity = abs($difference);
            $stockMovement->balance = $quantity;
            $stockMovement->unit_id = Product::findOne($productId)->unit_id;
            $stockMovement->movement_date = date('Y-m-d H:i:s');
            $stockMovement->created_at = date('Y-m-d H:i:s');
            $stockMovement->created_by = Yii::$app->user->id;
            $stockMovement->note = 'Điều chỉnh tồn kho: ' . $reason;
            
            if ($difference > 0) {
                // Nhập kho điều chỉnh
                $stockMovement->movement_type = StockMovement::TYPE_IN;
                $stockMovement->destination_warehouse_id = $warehouseId;
                $stockMovement->reference_type = 'adjustment_in';
            } else {
                // Xuất kho điều chỉnh
                $stockMovement->movement_type = StockMovement::TYPE_OUT;
                $stockMovement->source_warehouse_id = $warehouseId;
                $stockMovement->reference_type = 'adjustment_out';
            }
            
            if (!$stockMovement->save()) {
                throw new \Exception('Không thể lưu lịch sử chuyển động kho.');
            }
            
            $transaction->commit();
            
            return [
                'success' => true,
                'message' => 'Điều chỉnh tồn kho thành công.',
                'new_quantity' => $quantity
            ];
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Finds the Warehouse model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Warehouse the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findWarehouseModel($id)
    {
        if (($model = Warehouse::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Không tìm thấy kho hàng.');
    }
}