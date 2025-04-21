<?php

namespace backend\controllers;

use Yii;
use common\models\Order;
use common\models\OrderDetail;
use common\models\Customer;
use common\models\Product;
use common\models\ProductUnit;
use common\models\PaymentMethod;
use common\models\OrderSearch;
use common\models\ReturnSearch;
use common\components\AccessControl; 
use common\components\WarehouseFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use backend\services\OrderService;
use backend\services\ReturnService;

/**
 * SalesController implements actions for sales management.
 */
class SalesController extends Controller
{
    private $orderService;
    private $returnService;

    public function __construct($id, $module, OrderService $orderService, ReturnService $returnService, $config = [])
    {
        $this->orderService = $orderService;
        $this->returnService = $returnService;
        parent::__construct($id, $module, $config);
    }

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
                        'actions' => ['index', 'view-order'],
                        'permissions' => 'viewOrders',
                    ],
                    [
                        'actions' => ['create-order', 'pos'],
                        'permissions' => ['createOrder', 'accessPOS'],
                    ],
                    [
                        'actions' => ['update-order'],
                        'permissions' => 'updateOrder',
                    ],
                    [
                        'actions' => ['cancel-order'],
                        'permissions' => 'cancelOrder',
                    ],
                    [
                        'actions' => ['returns', 'create-return', 'view-return', 'process-return'],
                        'permissions' => 'processReturn',
                    ],
                    [
                        'actions' => ['get-product', 'get-customer', 'print-order', 'print-return'],
                        'permissions' => ['viewProducts', 'viewCustomers', 'viewOrders'],
                    ],
                    [
                        'actions' => ['discounts', 'create-discount', 'update-discount', 'delete-discount'],
                        'permissions' => 'manageDiscounts',
                    ],
                ],
            ],
            'warehouseFilter' => [
                'class' => WarehouseFilter::className(),
                'only' => ['create-order', 'view-order', 'update-order', 'cancel-order', 'create-return'],
                'except' => ['index', 'pos', 'returns', 'get-product', 'get-customer'],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'cancel-order' => ['POST'],
                    'delete-discount' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Order models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Nếu không phải admin hoặc quản lý cửa hàng, chỉ xem được kho được phân quyền
        if (!Yii::$app->user->can('admin') && !Yii::$app->user->can('storeManager')) {
            $userWarehouses = \common\models\UserWarehouse::find()
                ->select('warehouse_id')
                ->where(['user_id' => Yii::$app->user->id])
                ->column();

            $dataProvider->query->andWhere(['warehouse_id' => $userWarehouses]);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays POS interface
     * @return mixed
     */
    public function actionPos()
    {
        // Kiểm tra ca làm việc đang mở
        $currentShift = \common\models\Shift::find()
            ->where(['user_id' => Yii::$app->user->id, 'status' => 0]) // 0 = open
            ->one();

        if (!$currentShift) {
            Yii::$app->session->setFlash('warning', 'Bạn cần mở ca làm việc trước khi bán hàng.');
            return $this->redirect(['/shift/open']);
        }

        // Tạo đơn hàng mới
        $order = new Order();
        $order->user_id = Yii::$app->user->id;
        $order->shift_id = $currentShift->id;
        $order->warehouse_id = $currentShift->warehouse_id;
        $order->order_date = date('Y-m-d H:i:s');
        $order->status = Order::STATUS_DRAFT;
        $order->payment_status = Order::PAYMENT_STATUS_UNPAID;

        // Lấy danh sách sản phẩm
        $categories = \common\models\ProductCategory::find()
            ->where(['status' => 1])
            ->all();

        $paymentMethods = ArrayHelper::map(
            PaymentMethod::find()->where(['is_active' => 1])->orderBy('sort_order')->all(),
            'id',
            'name'
        );

        return $this->render('pos', [
            'order' => $order,
            'categories' => $categories,
            'paymentMethods' => $paymentMethods,
            'warehouse' => $currentShift->warehouse,
        ]);
    }

    /**
     * Displays a single Order model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionViewOrder($id)
    {
        $model = $this->findOrderModel($id);
        return $this->render('view-order', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Order model.
     * @param integer $warehouse_id
     * @return mixed
     */
    public function actionCreateOrder($warehouse_id)
    {
        $model = new Order();
        $model->user_id = Yii::$app->user->id;
        $model->warehouse_id = $warehouse_id;
        $model->order_date = date('Y-m-d H:i:s');
        $model->status = Order::STATUS_DRAFT;
        $model->payment_status = Order::PAYMENT_STATUS_UNPAID;
        $model->created_at = date('Y-m-d H:i:s');
        $model->updated_at = date('Y-m-d H:i:s');

        // Generate order code
        $model->code = $this->orderService->generateOrderCode();

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    // Save order details
                    $details = Yii::$app->request->post('OrderDetail', []);
                    $this->orderService->saveOrderDetails($model->id, $details);

                    // Process payment if paid
                    $paymentAmount = Yii::$app->request->post('payment_amount', 0);
                    if ($paymentAmount > 0) {
                        $this->orderService->processPayment($model, $paymentAmount);
                    }

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Đơn hàng đã được tạo thành công.');
                    return $this->redirect(['view-order', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            }
        }

        // Get warehouses
        $warehouses = [];
        if (Yii::$app->user->can('admin') || Yii::$app->user->can('storeManager')) {
            $warehouses = ArrayHelper::map(
                \common\models\Warehouse::find()->where(['is_active' => 1])->all(),
                'id',
                'name'
            );
        } else {
            $userWarehouses = \common\models\UserWarehouse::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->all();

            $warehouseIds = ArrayHelper::getColumn($userWarehouses, 'warehouse_id');
            $warehouses = ArrayHelper::map(
                \common\models\Warehouse::find()->where(['id' => $warehouseIds, 'is_active' => 1])->all(),
                'id',
                'name'
            );
        }

        return $this->render('create-order', [
            'model' => $model,
            'warehouses' => $warehouses,
            'customers' => ArrayHelper::map(Customer::find()->where(['status' => 1])->all(), 'id', 'name'),
            'products' => ArrayHelper::map(Product::find()->where(['status' => 1])->all(), 'id', 'name'),
            'units' => ArrayHelper::map(ProductUnit::find()->all(), 'id', 'name'),
            'paymentMethods' => ArrayHelper::map(PaymentMethod::find()->where(['is_active' => 1])->all(), 'id', 'name'),
        ]);
    }

    /**
     * Updates an existing Order model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdateOrder($id)
    {
        $model = $this->findOrderModel($id);

        // Check if order can be updated
        if ($model->status != Order::STATUS_DRAFT && $model->status != Order::STATUS_CONFIRMED) {
            Yii::$app->session->setFlash('error', 'Không thể cập nhật đơn hàng đã hoàn thành hoặc đã hủy.');
            return $this->redirect(['view-order', 'id' => $model->id]);
        }

        $model->updated_at = date('Y-m-d H:i:s');

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    // Save order details
                    OrderDetail::deleteAll(['order_id' => $model->id]);
                    $details = Yii::$app->request->post('OrderDetail', []);
                    $this->orderService->saveOrderDetails($model->id, $details);

                    // Process payment if needed
                    $paymentAmount = Yii::$app->request->post('payment_amount', 0);
                    if ($paymentAmount > 0) {
                        $this->orderService->processPayment($model, $paymentAmount);
                    }

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Đơn hàng đã được cập nhật thành công.');
                    return $this->redirect(['view-order', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            }
        }

        return $this->render('update-order', [
            'model' => $model,
            'warehouses' => ArrayHelper::map(\common\models\Warehouse::find()->where(['is_active' => 1])->all(), 'id', 'name'),
            'customers' => ArrayHelper::map(Customer::find()->where(['status' => 1])->all(), 'id', 'name'),
            'products' => ArrayHelper::map(Product::find()->where(['status' => 1])->all(), 'id', 'name'),
            'units' => ArrayHelper::map(ProductUnit::find()->all(), 'id', 'name'),
            'paymentMethods' => ArrayHelper::map(PaymentMethod::find()->where(['is_active' => 1])->all(), 'id', 'name'),
        ]);
    }

    /**
     * Cancels an Order model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCancelOrder($id)
    {
        $model = $this->findOrderModel($id);

        // Check if order can be canceled
        if ($model->status == Order::STATUS_COMPLETED || $model->status == Order::STATUS_CANCELED) {
            Yii::$app->session->setFlash('error', 'Không thể hủy đơn hàng đã hoàn thành hoặc đã hủy.');
            return $this->redirect(['view-order', 'id' => $model->id]);
        }

        $result = $this->orderService->cancelOrder($model);

        if ($result['success']) {
            Yii::$app->session->setFlash('success', $result['message']);
        } else {
            Yii::$app->session->setFlash('error', $result['message']);
        }

        return $this->redirect(['view-order', 'id' => $model->id]);
    }

    /**
     * Lists all Return models.
     * @return mixed
     */
    public function actionReturns()
    {
        $searchModel = new ReturnSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Nếu không phải admin hoặc quản lý cửa hàng, chỉ xem được kho được phân quyền
        if (!Yii::$app->user->can('admin') && !Yii::$app->user->can('storeManager')) {
            $userWarehouses = \common\models\UserWarehouse::find()
                ->select('warehouse_id')
                ->where(['user_id' => Yii::$app->user->id])
                ->column();

            $dataProvider->query->andWhere(['warehouse_id' => $userWarehouses]);
        }

        return $this->render('returns', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Return model.
     * @param integer $order_id
     * @return mixed
     */
    public function actionCreateReturn($order_id = null)
    {
        $model = new \common\models\Return();
        $model->user_id = Yii::$app->user->id;
        $model->return_date = date('Y-m-d H:i:s');
        $model->status = \common\models\Return::STATUS_DRAFT;
        $model->created_at = date('Y-m-d H:i:s');
        $model->updated_at = date('Y-m-d H:i:s');

        // Generate return code
        $model->code = $this->returnService->generateReturnCode();

        // If returning from specific order
        if ($order_id) {
            $order = Order::findOne($order_id);
            if ($order) {
                $model->order_id = $order_id;
                $model->customer_id = $order->customer_id;
                $model->warehouse_id = $order->warehouse_id;
            }
        }

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    // Save return details
                    $details = Yii::$app->request->post('ReturnDetail', []);
                    $this->returnService->saveReturnDetails($model->id, $details);

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Phiếu trả hàng đã được tạo thành công.');
                    return $this->redirect(['view-return', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            }
        }

        // Get warehouses
        $warehouses = [];
        if (Yii::$app->user->can('admin') || Yii::$app->user->can('storeManager')) {
            $warehouses = ArrayHelper::map(
                \common\models\Warehouse::find()->where(['is_active' => 1])->all(),
                'id',
                'name'
            );
        } else {
            $userWarehouses = \common\models\UserWarehouse::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->all();

            $warehouseIds = ArrayHelper::getColumn($userWarehouses, 'warehouse_id');
            $warehouses = ArrayHelper::map(
                \common\models\Warehouse::find()->where(['id' => $warehouseIds, 'is_active' => 1])->all(),
                'id',
                'name'
            );
        }

        return $this->render('create-return', [
            'model' => $model,
            'warehouses' => $warehouses,
            'customers' => ArrayHelper::map(Customer::find()->where(['status' => 1])->all(), 'id', 'name'),
            'products' => ArrayHelper::map(Product::find()->where(['status' => 1])->all(), 'id', 'name'),
            'units' => ArrayHelper::map(ProductUnit::find()->all(), 'id', 'name'),
            'paymentMethods' => ArrayHelper::map(PaymentMethod::find()->where(['is_active' => 1])->all(), 'id', 'name'),
            'orderDetails' => $order_id ? OrderDetail::find()->where(['order_id' => $order_id])->all() : [],
        ]);
    }

    /**
     * Displays a single Return model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionViewReturn($id)
    {
        $model = $this->findReturnModel($id);
        return $this->render('view-return', [
            'model' => $model,
        ]);
    }

    /**
     * Process a Return model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionProcessReturn($id)
    {
        $model = $this->findReturnModel($id);

        // Check if return can be processed
        if ($model->status != \common\models\Return::STATUS_DRAFT && $model->status != \common\models\Return::STATUS_CONFIRMED) {
            Yii::$app->session->setFlash('error', 'Không thể xử lý phiếu trả hàng đã hoàn thành hoặc đã hủy.');
            return $this->redirect(['view-return', 'id' => $model->id]);
        }

        $result = $this->returnService->processReturn($model);

        if ($result['success']) {
            Yii::$app->session->setFlash('success', $result['message']);
        } else {
            Yii::$app->session->setFlash('error', $result['message']);
        }

        return $this->redirect(['view-return', 'id' => $model->id]);
    }

    /**
     * Get product information via AJAX
     * @param integer $id
     * @param integer $warehouse_id
     * @return string JSON response
     */
    public function actionGetProduct($id, $warehouse_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $product = Product::findOne($id);
        if (!$product) {
            return ['error' => 'Không tìm thấy sản phẩm.'];
        }

        // Get stock information
        $stock = \common\models\Stock::findOne(['product_id' => $id, 'warehouse_id' => $warehouse_id]);
        $quantity = $stock ? $stock->quantity : 0;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'barcode' => $product->barcode,
            'unit_id' => $product->unit_id,
            'unit_name' => $product->unit ? $product->unit->name : '',
            'selling_price' => $product->selling_price,
            'quantity' => $quantity,
            'is_combo' => $product->is_combo,
        ];
    }

    /**
     * Get customer information via AJAX
     * @param integer $id
     * @return string JSON response
     */
    public function actionGetCustomer($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $customer = Customer::findOne($id);
        if (!$customer) {
            return ['error' => 'Không tìm thấy khách hàng.'];
        }

        // Get customer points
        $customerPoint = \common\models\CustomerPoint::findOne(['customer_id' => $id]);
        $points = $customerPoint ? $customerPoint->points : 0;

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'address' => $customer->address,
            'points' => $points,
            'group_id' => $customer->customer_group_id,
            'group_name' => $customer->customerGroup ? $customer->customerGroup->name : '',
            'discount_rate' => $customer->customerGroup ? $customer->customerGroup->discount_rate : 0,
        ];
    }

    /**
     * Print order
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionPrintOrder($id)
    {
        $model = $this->findOrderModel($id);

        return $this->renderPartial('print-order', [
            'model' => $model,
        ]);
    }

    /**
     * Print return
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionPrintReturn($id)
    {
        $model = $this->findReturnModel($id);

        return $this->renderPartial('print-return', [
            'model' => $model,
        ]);
    }

    /**
     * List discounts
     * @return mixed
     */
    public function actionDiscounts()
    {
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => \common\models\Discount::find(),
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('discounts', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Create discount
     * @return mixed
     */
    public function actionCreateDiscount()
    {
        $model = new \common\models\Discount();
        $model->is_active = 1;
        $model->created_at = date('Y-m-d H:i:s');
        $model->updated_at = date('Y-m-d H:i:s');
        $model->created_by = Yii::$app->user->id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Save product discounts
            $productIds = Yii::$app->request->post('product_ids', []);
            $categoryIds = Yii::$app->request->post('category_ids', []);

            foreach ($productIds as $productId) {
                $productDiscount = new \common\models\ProductDiscount();
                $productDiscount->discount_id = $model->id;
                $productDiscount->product_id = $productId;
                $productDiscount->created_at = date('Y-m-d H:i:s');
                $productDiscount->save();
            }

            foreach ($categoryIds as $categoryId) {
                $productDiscount = new \common\models\ProductDiscount();
                $productDiscount->discount_id = $model->id;
                $productDiscount->product_category_id = $categoryId;
                $productDiscount->created_at = date('Y-m-d H:i:s');
                $productDiscount->save();
            }

            Yii::$app->session->setFlash('success', 'Chương trình giảm giá đã được tạo thành công.');
            return $this->redirect(['discounts']);
        }

        return $this->render('create-discount', [
            'model' => $model,
            'products' => Product::find()->where(['status' => 1])->all(),
            'categories' => \common\models\ProductCategory::find()->where(['status' => 1])->all(),
        ]);
    }

    /**
     * Update discount
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdateDiscount($id)
    {
        $model = \common\models\Discount::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Không tìm thấy chương trình giảm giá.');
        }

        $model->updated_at = date('Y-m-d H:i:s');

        // Get current product/category associations
        $productDiscounts = \common\models\ProductDiscount::find()->where(['discount_id' => $id])->all();
        $productIds = [];
        $categoryIds = [];

        foreach ($productDiscounts as $pd) {
            if ($pd->product_id) {
                $productIds[] = $pd->product_id;
            }
            if ($pd->product_category_id) {
                $categoryIds[] = $pd->product_category_id;
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Update product discounts
            \common\models\ProductDiscount::deleteAll(['discount_id' => $model->id]);

            $productIds = Yii::$app->request->post('product_ids', []);
            $categoryIds = Yii::$app->request->post('category_ids', []);

            foreach ($productIds as $productId) {
                $productDiscount = new \common\models\ProductDiscount();
                $productDiscount->discount_id = $model->id;
                $productDiscount->product_id = $productId;
                $productDiscount->product_id = $productId;
                $productDiscount->product_category_id = null;
                $productDiscount->created_at = date('Y-m-d H:i:s');
                $productDiscount->save();
            }

            foreach ($categoryIds as $categoryId) {
                $productDiscount = new \common\models\ProductDiscount();
                $productDiscount->discount_id = $model->id;
                $productDiscount->product_id = null;
                $productDiscount->product_category_id = $categoryId;
                $productDiscount->created_at = date('Y-m-d H:i:s');
                $productDiscount->save();
            }

            Yii::$app->session->setFlash('success', 'Chương trình giảm giá đã được cập nhật thành công.');
            return $this->redirect(['discounts']);
        }

        return $this->render('update-discount', [
            'model' => $model,
            'products' => Product::find()->where(['status' => 1])->all(),
            'categories' => \common\models\ProductCategory::find()->where(['status' => 1])->all(),
            'selectedProducts' => $productIds,
            'selectedCategories' => $categoryIds,
        ]);
    }

    /**
     * Delete discount
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDeleteDiscount($id)
    {
        $model = \common\models\Discount::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Không tìm thấy chương trình giảm giá.');
        }

        // Xóa các liên kết với sản phẩm/danh mục
        \common\models\ProductDiscount::deleteAll(['discount_id' => $id]);

        $model->delete();

        Yii::$app->session->setFlash('success', 'Chương trình giảm giá đã được xóa thành công.');
        return $this->redirect(['discounts']);
    }

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findOrderModel($id)
    {
        if (($model = Order::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Không tìm thấy đơn hàng.');
    }

    /**
     * Finds the Return model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return \common\models\Return the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findReturnModel($id)
    {
        if (($model = \common\models\Return::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Không tìm thấy phiếu trả hàng.');
    }
}
