<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use common\models\Product;
use common\models\ProductCategory;
use common\models\Warehouse;
use common\models\Stock;
use common\models\Order;
use common\models\OrderDetail;
use common\models\Customer;
use common\models\CustomerPoint;
use common\models\CustomerPointHistory;
use common\models\Shift;
use common\models\ShiftDetail;
use common\models\Payment;
use common\models\PaymentMethod;
use common\models\Discount;
use common\models\StockMovement;
use common\models\StockOut;
use common\models\StockOutDetail;
use common\models\Warranty;
use common\models\ProductBatch;
use common\models\ProductImage;
/**
 * POS controller for frontend
 */
class PosController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        // Set layout for POS
        $this->layout = 'pos';
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
                        'actions' => [
                            'index',
                            'get-products',
                            'search-products',
                            'search-customers',
                            'add-customer',
                            'create-order',
                            'get-recent-orders',
                            'get-hold-orders',
                            'load-order',
                            'check-shift',
                            'start-shift',
                            'end-shift',
                            'get-shift-details',
                            'hold-order',
                            'delete-hold-order',
                            'apply-discount',
                            'print-receipt',
                            'print-warranty',
                            'get-product'
                        ],
                        'allow' => true,
                        'roles' => ['@'], // @ means authenticated users
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'create-order' => ['post'],
                    'add-customer' => ['post'],
                    'start-shift' => ['post'],
                    'end-shift' => ['post'],
                    'hold-order' => ['post'],
                    'delete-hold-order' => ['post'],
                    'apply-discount' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Main POS view
     * @return mixed
     */
    public function actionIndex()
    {
        // Check if user has an active shift
        $userId = Yii::$app->user->id;
        $currentShift = Shift::find()
            ->where(['user_id' => $userId, 'status' => 0]) // 0 = open shift
            ->one();

        // If no active shift, redirect to start shift page
        if ($currentShift === null) {
            return $this->redirect(['check-shift']);
        }

        // Get the warehouse assigned to this shift
        $warehouse = Warehouse::findOne($currentShift->warehouse_id);
        if ($warehouse === null) {
            $warehouse = Warehouse::find()->where(['is_default' => 1])->one();
        }

        // Get all active categories for the product filter
        $categories = ProductCategory::find()
            ->where(['status' => 1])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();

        // Get all active payment methods
        $paymentMethods = PaymentMethod::find()
            ->where(['is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();

        return $this->render('index', [
            'currentShift' => $currentShift,
            'warehouse' => $warehouse,
            'categories' => $categories,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Check shift status and redirect accordingly
     * @return mixed
     */
    public function actionCheckShift()
    {
        $userId = Yii::$app->user->id;
        $currentShift = Shift::find()
            ->where(['user_id' => $userId, 'status' => 0]) // 0 = open shift
            ->one();

        if ($currentShift !== null) {
            return $this->redirect(['index']); // Redirect to POS if shift is active
        }

        // Get all active warehouses for the start shift form
        $warehouses = Warehouse::find()
            ->where(['is_active' => 1])
            ->all();

        return $this->render('start-shift', [
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Start a new shift
     * @return \yii\web\Response
     */
    public function actionStartShift()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $post = Yii::$app->request->post();
        $userId = Yii::$app->user->id;

        // Check if user already has an active shift
        $existingShift = Shift::find()
            ->where(['user_id' => $userId, 'status' => 0])
            ->one();

        if ($existingShift !== null) {
            return [
                'success' => false,
                'message' => 'Bạn đã có ca làm việc đang hoạt động.'
            ];
        }

        // Validate warehouse exists
        $warehouseId = $post['warehouse_id'] ?? null;
        $warehouse = Warehouse::findOne($warehouseId);
        if ($warehouse === null) {
            return [
                'success' => false,
                'message' => 'Kho hàng không tồn tại.'
            ];
        }

        // Create new shift
        $shift = new Shift();
        $shift->user_id = $userId;
        $shift->warehouse_id = $warehouseId;
        $shift->cashier_id = $userId; // Cashier is the same as user for now
        $shift->start_time = date('Y-m-d H:i:s');
        $shift->opening_amount = $post['opening_amount'] ?? 0;
        $shift->status = 0; // 0 = open
        $shift->created_at = date('Y-m-d H:i:s');
        $shift->updated_at = date('Y-m-d H:i:s');

        if ($shift->save()) {
            return [
                'success' => true,
                'message' => 'Ca làm việc đã được bắt đầu.',
                'shift_id' => $shift->id
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Không thể bắt đầu ca làm việc: ' . Json::encode($shift->errors)
            ];
        }
    }

    /**
     * End current shift
     * @return \yii\web\Response
     */
    public function actionEndShift()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $post = Yii::$app->request->post();
        $userId = Yii::$app->user->id;

        // Find current active shift
        $shift = Shift::find()
            ->where(['user_id' => $userId, 'status' => 0])
            ->one();

        if ($shift === null) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy ca làm việc đang hoạt động.'
            ];
        }

        // Update shift details
        $shift->end_time = date('Y-m-d H:i:s');
        $shift->actual_amount = $post['actual_amount'] ?? 0;
        $shift->difference = ($post['actual_amount'] ?? 0) - $shift->expected_amount;
        $shift->explanation = $post['explanation'] ?? null;
        $shift->status = 1; // 1 = closed
        $shift->updated_at = date('Y-m-d H:i:s');

        if ($shift->save()) {
            return [
                'success' => true,
                'message' => 'Ca làm việc đã được kết thúc.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Không thể kết thúc ca làm việc: ' . Json::encode($shift->errors)
            ];
        }
    }

    /**
     * Get shift payment details
     * @return \yii\web\Response
     */
    public function actionGetShiftDetails()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $userId = Yii::$app->user->id;

        // Find current active shift
        $shift = Shift::find()
            ->where(['user_id' => $userId, 'status' => 0])
            ->one();

        if ($shift === null) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy ca làm việc đang hoạt động.'
            ];
        }

        // Get payment method details for this shift
        $shiftDetails = ShiftDetail::find()
            ->where(['shift_id' => $shift->id])
            ->all();

        $paymentDetails = [];

        foreach ($shiftDetails as $detail) {
            $paymentMethod = PaymentMethod::findOne($detail->payment_method_id);
            $methodName = $paymentMethod ? $paymentMethod->name : 'Unknown';

            if (!isset($paymentDetails[$methodName])) {
                $paymentDetails[$methodName] = [
                    'sales' => 0,
                    'returns' => 0,
                    'receipts' => 0,
                    'payments' => 0,
                ];
            }

            // Transaction type: 1: sales, 2: returns, 3: receipts, 4: payments
            switch ($detail->transaction_type) {
                case 1:
                    $paymentDetails[$methodName]['sales'] += $detail->total_amount;
                    break;
                case 2:
                    $paymentDetails[$methodName]['returns'] += $detail->total_amount;
                    break;
                case 3:
                    $paymentDetails[$methodName]['receipts'] += $detail->total_amount;
                    break;
                case 4:
                    $paymentDetails[$methodName]['payments'] += $detail->total_amount;
                    break;
            }
        }

        return [
            'success' => true,
            'shift' => [
                'id' => $shift->id,
                'start_time' => Yii::$app->formatter->asDatetime($shift->start_time),
                'opening_amount' => $shift->opening_amount,
                'total_sales' => $shift->total_sales,
                'total_returns' => $shift->total_returns,
                'total_receipts' => $shift->total_receipts,
                'total_payments' => $shift->total_payments,
                'expected_amount' => $shift->expected_amount,
            ],
            'payment_details' => $paymentDetails
        ];
    }

    /**
     * Get products for POS
     * @return \yii\web\Response
     */
    public function actionGetProducts()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        $warehouseId = $request->get('warehouse_id');
        $categoryId = $request->get('category_id');
        $page = $request->get('page', 1);
        $pageSize = $request->get('page_size', 20);

        $query = Product::find()
            ->where(['status' => 1]); // Only active products

        if ($categoryId) {
            $query->andWhere(['category_id' => $categoryId]);
        }

        $totalProducts = $query->count();

        $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->orderBy(['name' => SORT_ASC]);

        $products = $query->all();

        $result = [];
        foreach ($products as $product) {
            // Get stock for this product in the selected warehouse
            $stock = Stock::find()
                ->where(['product_id' => $product->id, 'warehouse_id' => $warehouseId])
                ->one();

            $quantity = $stock ? $stock->quantity : 0;

            $mainImage = null;
            $productImages = ProductImage::find()
                ->where(['product_id' => $product->id, 'is_main' => 1])
                ->one();

            $imageUrl = $productImages ? Yii::getAlias('@web/uploads/products/' . $productImages->image) : Yii::getAlias('@web/images/no-image.png');

            $result[] = [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'price' => $product->selling_price,
                'formatted_price' => Yii::$app->formatter->asCurrency($product->selling_price),
                'image' => $imageUrl,
                'quantity' => $quantity,
                'unit' => $product->unit ? $product->unit->name : '',
                'category_id' => $product->category_id,
                'barcode' => $product->barcode,
            ];
        }

        return [
            'success' => true,
            'products' => $result,
            'total' => $totalProducts,
            'page' => $page,
            'page_size' => $pageSize,
            'total_pages' => ceil($totalProducts / $pageSize),
        ];
    }

    /**
 * Get product details by ID
 * @param int $id
 * @return \yii\web\Response
 */
public function actionGetProduct($id)
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    
    $product = Product::findOne($id);
    
    if (!$product) {
        return [
            'success' => false,
            'message' => 'Sản phẩm không tồn tại.'
        ];
    }
    
    // Get warehouse from current shift or from request
    $warehouseId = Yii::$app->request->get('warehouse_id');
    if (!$warehouseId) {
        $userId = Yii::$app->user->id;
        $currentShift = Shift::find()
            ->where(['user_id' => $userId, 'status' => 0])
            ->one();
            
        if ($currentShift) {
            $warehouseId = $currentShift->warehouse_id;
        }
    }
    
    // Get stock quantity
    $stock = Stock::find()
        ->where(['product_id' => $product->id, 'warehouse_id' => $warehouseId])
        ->one();
    
    $quantity = $stock ? $stock->quantity : 0;
    
    // Get product images
    $productImages = ProductImage::find()
        ->where(['product_id' => $product->id, 'is_main' => 1])
        ->one();
    
    $imageUrl = $productImages ? Yii::getAlias('@web/uploads/products/' . $productImages->image) : Yii::getAlias('@web/images/no-image.png');
    
    // Get unit name
    $unit = $product->unit ? $product->unit->name : '';
    
    return [
        'success' => true,
        'product' => [
            'id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'price' => $product->selling_price,
            'formatted_price' => Yii::$app->formatter->asCurrency($product->selling_price),
            'cost_price' => $product->cost_price,
            'image' => $imageUrl,
            'quantity' => $quantity,
            'unit' => $unit,
            'category_id' => $product->category_id,
            'barcode' => $product->barcode,
            'is_combo' => $product->is_combo,
            'warranty_period' => $product->warranty_period,
        ]
    ];
}

    /**
     * Search products by code, name or barcode
     * @return \yii\web\Response
     */
    public function actionSearchProducts()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $term = Yii::$app->request->get('term', '');
        
        if (empty($term)) {
            return [
                'success' => false,
                'message' => 'Từ khóa tìm kiếm trống',
            ];
        }
        
        // Tìm kiếm theo mã, tên hoặc mã vạch
        $query = Product::find()
            ->where(['or',
                ['like', 'code', $term],
                ['like', 'name', $term],
                ['like', 'barcode', $term]
            ])
            ->andWhere(['status' => 1]);
            
        // Thực thi query để lấy các model thực tế
        $products = $query->all();
        
        $result = [];
        foreach ($products as $product) {
            // Bây giờ truy cập thuộc tính từ model, không phải từ query
            $result[] = [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'selling_price' => $product->selling_price,
                'formatted_price' => Yii::$app->formatter->asCurrency($product->selling_price),
                'quantity' => $product->getStockQuantity(), // Giả sử bạn có một phương thức để lấy số lượng tồn kho
                'unit' => $product->unit ? $product->unit->name : '',
                'image' => $product->getProductImages(), // Giả sử bạn có một phương thức để lấy URL của hình ảnh
            ];
        }
        
        return [
            'success' => true,
            'products' => $result,
        ];
    }

    /**
     * Search customers by name or phone
     * @return \yii\web\Response
     */
    public function actionSearchCustomers()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        $term = $request->get('term');

        if (empty($term)) {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập từ khóa tìm kiếm.'
            ];
        }

        $query = Customer::find()
            ->where(['status' => 1])
            ->andWhere([
                'or',
                ['like', 'name', $term],
                ['like', 'phone', $term],
                ['like', 'email', $term]
            ])
            ->limit(10)
            ->orderBy(['name' => SORT_ASC]);

        $customers = $query->all();

        $result = [];
        foreach ($customers as $customer) {
            // Get customer points
            $points = $customer->getPoints();

            // Get customer debt
            $debt = $customer->debt_amount;

            $result[] = [
                'id' => $customer->id,
                'code' => $customer->code,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'address' => $customer->address,
                'points' => $points,
                'debt' => $debt,
                'formatted_debt' => Yii::$app->formatter->asCurrency($debt),
            ];
        }

        return [
            'success' => true,
            'customers' => $result,
        ];
    }

    /**
     * Add new customer
     * @return \yii\web\Response
     */
    public function actionAddCustomer()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $post = Yii::$app->request->post();

        // Validate required fields
        if (empty($post['name']) || empty($post['phone'])) {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập đầy đủ thông tin bắt buộc (Tên và SĐT).'
            ];
        }

        // Check if phone already exists
        $existingCustomer = Customer::findOne(['phone' => $post['phone']]);
        if ($existingCustomer !== null) {
            return [
                'success' => false,
                'message' => 'Số điện thoại này đã tồn tại trong hệ thống.'
            ];
        }

        // Generate customer code
        $lastCustomer = Customer::find()
            ->orderBy(['id' => SORT_DESC])
            ->one();

        $customerCode = 'KH00001';
        if ($lastCustomer) {
            $lastCode = (int)substr($lastCustomer->code, 2);
            $customerCode = 'KH' . str_pad($lastCode + 1, 5, '0', STR_PAD_LEFT);
        }

        // Create new customer
        $customer = new Customer();
        $customer->code = $customerCode;
        $customer->name = $post['name'];
        $customer->phone = $post['phone'];
        $customer->email = $post['email'] ?? null;
        $customer->address = $post['address'] ?? null;
        $customer->status = 1;
        $customer->created_at = date('Y-m-d H:i:s');
        $customer->updated_at = date('Y-m-d H:i:s');
        $customer->created_by = Yii::$app->user->id;

        if ($customer->save()) {
            return [
                'success' => true,
                'message' => 'Khách hàng đã được thêm thành công.',
                'customer' => [
                    'id' => $customer->id,
                    'code' => $customer->code,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'address' => $customer->address,
                    'points' => 0,
                    'debt' => 0,
                    'formatted_debt' => Yii::$app->formatter->asCurrency(0),
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Không thể thêm khách hàng: ' . Json::encode($customer->errors)
            ];
        }
    }

    /**
     * Create new order
     * @return \yii\web\Response
     */
    public function actionCreateOrder()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $post = Yii::$app->request->post();
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Check if user has an active shift
            $userId = Yii::$app->user->id;
            $currentShift = Shift::find()
                ->where(['user_id' => $userId, 'status' => 0])
                ->one();

            if ($currentShift === null) {
                throw new \Exception('Bạn không có ca làm việc nào đang hoạt động.');
            }

            // Get warehouse
            $warehouseId = $post['warehouse_id'] ?? $currentShift->warehouse_id;
            $warehouse = Warehouse::findOne($warehouseId);
            if ($warehouse === null) {
                throw new \Exception('Kho hàng không tồn tại.');
            }

            // Validate cart items
            $cartItems = $post['cart_items'] ?? [];
            if (empty($cartItems)) {
                throw new \Exception('Giỏ hàng không có sản phẩm nào.');
            }

            // Generate order code
            $lastOrder = Order::find()
                ->orderBy(['id' => SORT_DESC])
                ->one();

            $orderCode = 'DH00001';
            if ($lastOrder) {
                $lastCode = (int)substr($lastOrder->code, 2);
                $orderCode = 'DH' . str_pad($lastCode + 1, 5, '0', STR_PAD_LEFT);
            }

            // Create new order
            $order = new Order();
            $order->code = $orderCode;
            $order->customer_id = $post['customer_id'] ?? null;
            $order->user_id = $userId;
            $order->shift_id = $currentShift->id;
            $order->warehouse_id = $warehouseId;
            $order->order_date = date('Y-m-d H:i:s');
            $order->total_quantity = $post['total_quantity'] ?? 0;
            $order->subtotal = $post['subtotal'] ?? 0;
            $order->discount_amount = $post['discount_amount'] ?? 0;
            $order->tax_amount = $post['tax_amount'] ?? 0;
            $order->total_amount = $post['total_amount'] ?? 0;
            $order->paid_amount = $post['paid_amount'] ?? 0;
            $order->change_amount = $post['change_amount'] ?? 0;
            $order->points_earned = $post['points_earned'] ?? 0;
            $order->points_used = $post['points_used'] ?? 0;
            $order->points_amount = $post['points_amount'] ?? 0;
            $order->payment_method_id = $post['payment_method_id'] ?? null;
            $order->payment_status = ($post['paid_amount'] >= $post['total_amount']) ? 2 : 0; // 2 = paid, 0 = unpaid
            $order->status = 2; // 2 = paid
            $order->created_at = date('Y-m-d H:i:s');
            $order->updated_at = date('Y-m-d H:i:s');

            if (!$order->save()) {
                throw new \Exception('Không thể tạo đơn hàng: ' . Json::encode($order->errors));
            }

            // Create stock out record
            $stockOut = new StockOut();
            $stockOut->code = 'XK' . substr($orderCode, 2); // Same code as order but with XK prefix
            $stockOut->warehouse_id = $warehouseId;
            $stockOut->reference_id = $order->id;
            $stockOut->reference_type = 'order';
            $stockOut->stock_out_date = date('Y-m-d H:i:s');
            $stockOut->total_amount = $order->total_amount;
            $stockOut->status = 2; // 2 = completed
            $stockOut->created_at = date('Y-m-d H:i:s');
            $stockOut->updated_at = date('Y-m-d H:i:s');
            $stockOut->created_by = $userId;

            if (!$stockOut->save()) {
                throw new \Exception('Không thể tạo phiếu xuất kho: ' . Json::encode($stockOut->errors));
            }

            // Process cart items
            $totalCostPrice = 0;

            foreach ($cartItems as $item) {
                $product = Product::findOne($item['product_id']);
                if ($product === null) {
                    throw new \Exception('Sản phẩm không tồn tại: ' . $item['product_id']);
                }

                // Check stock
                $stock = Stock::find()
                    ->where(['product_id' => $product->id, 'warehouse_id' => $warehouseId])
                    ->one();

                if ($stock === null || $stock->quantity < $item['quantity']) {
                    throw new \Exception('Sản phẩm ' . $product->name . ' không đủ số lượng trong kho.');
                }

                // Create order detail
                $orderDetail = new OrderDetail();
                $orderDetail->order_id = $order->id;
                $orderDetail->product_id = $product->id;
                $orderDetail->quantity = $item['quantity'];
                $orderDetail->unit_id = $product->unit_id;
                $orderDetail->unit_price = $item['price'];
                $orderDetail->discount_percent = $item['discount_percent'] ?? 0;
                $orderDetail->discount_amount = $item['discount_amount'] ?? 0;
                $orderDetail->tax_percent = $item['tax_percent'] ?? 0;
                $orderDetail->tax_amount = $item['tax_amount'] ?? 0;
                $orderDetail->total_amount = $item['total'];
                $orderDetail->cost_price = $product->cost_price;

                if (!$orderDetail->save()) {
                    throw new \Exception('Không thể tạo chi tiết đơn hàng: ' . Json::encode($orderDetail->errors));
                }

                // Create stock out detail
                $stockOutDetail = new StockOutDetail();
                $stockOutDetail->stock_out_id = $stockOut->id;
                $stockOutDetail->product_id = $product->id;
                $stockOutDetail->quantity = $item['quantity'];
                $stockOutDetail->unit_id = $product->unit_id;
                $stockOutDetail->unit_price = $item['price'];
                $stockOutDetail->total_price = $item['total'];

                if (!$stockOutDetail->save()) {
                    throw new \Exception('Không thể tạo chi tiết xuất kho: ' . Json::encode($stockOutDetail->errors));
                }

                // Update stock
                $stock->quantity -= $item['quantity'];
                $stock->updated_at = date('Y-m-d H:i:s');

                if (!$stock->save()) {
                    throw new \Exception('Không thể cập nhật tồn kho: ' . Json::encode($stock->errors));
                }

                // Create stock movement
                $stockMovement = new StockMovement();
                $stockMovement->product_id = $product->id;
                $stockMovement->source_warehouse_id = $warehouseId;
                $stockMovement->reference_id = $order->id;
                $stockMovement->reference_type = 'order';
                $stockMovement->quantity = $item['quantity'];
                $stockMovement->balance = $stock->quantity;
                $stockMovement->unit_id = $product->unit_id;
                $stockMovement->movement_type = 2; // 2 = out
                $stockMovement->movement_date = date('Y-m-d H:i:s');
                $stockMovement->created_at = date('Y-m-d H:i:s');
                $stockMovement->created_by = $userId;

                if (!$stockMovement->save()) {
                    throw new \Exception('Không thể tạo lịch sử kho: ' . Json::encode($stockMovement->errors));
                }

                $totalCostPrice += ($product->cost_price * $item['quantity']);
            }

            // Create payment record if paid
            if ($order->payment_status == 2) {
                $payment = new Payment();
                $payment->order_id = $order->id;
                $payment->payment_method_id = $order->payment_method_id;
                $payment->amount = $order->paid_amount;
                $payment->payment_date = date('Y-m-d H:i:s');
                $payment->status = 1; // 1 = success
                $payment->created_at = date('Y-m-d H:i:s');
                $payment->created_by = $userId;

                if (!$payment->save()) {
                    throw new \Exception('Không thể tạo thanh toán: ' . Json::encode($payment->errors));
                }
            }

            // Update shift totals
            $currentShift->total_sales += $order->total_amount;
            $currentShift->expected_amount = $currentShift->opening_amount + $currentShift->total_sales
                - $currentShift->total_returns + $currentShift->total_receipts
                - $currentShift->total_payments;
            $currentShift->updated_at = date('Y-m-d H:i:s');

            if (!$currentShift->save()) {
                throw new \Exception('Không thể cập nhật ca làm việc: ' . Json::encode($currentShift->errors));
            }

            // Create shift detail record
            $shiftDetail = new ShiftDetail();
            $shiftDetail->shift_id = $currentShift->id;
            $shiftDetail->payment_method_id = $order->payment_method_id;
            $shiftDetail->transaction_type = 1; // 1 = sales
            $shiftDetail->total_amount = $order->total_amount;
            $shiftDetail->transaction_count = 1;

            if (!$shiftDetail->save()) {
                throw new \Exception('Không thể tạo chi tiết ca làm việc: ' . Json::encode($shiftDetail->errors));
            }

            // Process customer points if applicable
            if ($order->customer_id) {
                $this->processCustomerPoints($order, $order->subtotal);
            }

            // Generate warranty for products that have warranty period
            $this->generateWarranty($order);

            $transaction->commit();

            return [
                'success' => true,
                'message' => 'Đơn hàng đã được tạo thành công.',
                'order' => [
                    'id' => $order->id,
                    'code' => $order->code,
                    'total_amount' => $order->total_amount,
                    'formatted_total' => Yii::$app->formatter->asCurrency($order->total_amount),
                ]
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get recent orders
     * @return \yii\web\Response
     */
    public function actionGetRecentOrders()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $userId = Yii::$app->user->id;
        $shiftId = Yii::$app->request->get('shift_id');

        // Find current active shift if not provided
        if (!$shiftId) {
            $currentShift = Shift::find()
                ->where(['user_id' => $userId, 'status' => 0])
                ->one();

            if ($currentShift) {
                $shiftId = $currentShift->id;
            }
        }

        $query = Order::find()
            ->where(['shift_id' => $shiftId])
            ->andWhere(['status' => [2, 4]]) // 2 = paid, 4 = completed
            ->orderBy(['order_date' => SORT_DESC])
            ->limit(10);

        $orders = $query->all();

        $result = [];
        foreach ($orders as $order) {
            $customer = $order->customer;
            $customerName = $customer ? $customer->name : 'Khách lẻ';

            $result[] = [
                'id' => $order->id,
                'code' => $order->code,
                'order_date' => Yii::$app->formatter->asDatetime($order->order_date),
                'customer_name' => $customerName,
                'total_amount' => $order->total_amount,
                'formatted_total' => Yii::$app->formatter->asCurrency($order->total_amount),
                'status' => $order->status,
                'status_text' => $order->getStatusText(),
            ];
        }

        return [
            'success' => true,
            'orders' => $result,
        ];
    }

    /**
     * Get hold orders
     * @return \yii\web\Response
     */
    public function actionGetHoldOrders()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $userId = Yii::$app->user->id;

        $query = Order::find()
            ->where(['user_id' => $userId, 'status' => 0]) // 0 = draft (hold)
            ->orderBy(['order_date' => SORT_DESC]);

        $orders = $query->all();

        $result = [];
        foreach ($orders as $order) {
            $customer = $order->customer;
            $customerName = $customer ? $customer->name : 'Khách lẻ';

            $result[] = [
                'id' => $order->id,
                'code' => $order->code,
                'order_date' => Yii::$app->formatter->asDatetime($order->order_date),
                'customer_name' => $customerName,
                'total_amount' => $order->total_amount,
                'formatted_total' => Yii::$app->formatter->asCurrency($order->total_amount),
                'note' => $order->note,
            ];
        }

        return [
            'success' => true,
            'orders' => $result,
        ];
    }

    /**
     * Load order details
     * @return \yii\web\Response
     */
    public function actionLoadOrder($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $order = Order::findOne($id);

        if ($order === null) {
            return [
                'success' => false,
                'message' => 'Đơn hàng không tồn tại.'
            ];
        }

        $orderDetails = OrderDetail::find()
            ->where(['order_id' => $order->id])
            ->all();

        $customer = $order->customer;

        $items = [];
        foreach ($orderDetails as $detail) {
            $product = $detail->product;

            // Get product main image
            $mainImage = $product->getMainImage();
            $imageUrl = $mainImage ? Yii::getAlias('@web/uploads/products/' . $mainImage->image) : Yii::getAlias('@web/images/no-image.png');

            $items[] = [
                'product_id' => $detail->product_id,
                'code' => $product->code,
                'name' => $product->name,
                'quantity' => $detail->quantity,
                'price' => $detail->unit_price,
                'formatted_price' => Yii::$app->formatter->asCurrency($detail->unit_price),
                'discount_percent' => $detail->discount_percent,
                'discount_amount' => $detail->discount_amount,
                'tax_percent' => $detail->tax_percent,
                'tax_amount' => $detail->tax_amount,
                'total' => $detail->total_amount,
                'formatted_total' => Yii::$app->formatter->asCurrency($detail->total_amount),
                'unit' => $detail->unit ? $detail->unit->name : '',
                'image' => $imageUrl,
            ];
        }

        return [
            'success' => true,
            'order' => [
                'id' => $order->id,
                'code' => $order->code,
                'order_date' => Yii::$app->formatter->asDatetime($order->order_date),
                'customer_id' => $order->customer_id,
                'customer' => $customer ? [
                    'id' => $customer->id,
                    'code' => $customer->code,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'address' => $customer->address,
                    'points' => $customer->getCustomerPoints(),
                    'debt' => $customer->debt_amount,
                    'formatted_debt' => Yii::$app->formatter->asCurrency($customer->debt_amount),
                ] : null,
                'total_quantity' => $order->total_quantity,
                'subtotal' => $order->subtotal,
                'discount_amount' => $order->discount_amount,
                'tax_amount' => $order->tax_amount,
                'total_amount' => $order->total_amount,
                'formatted_total' => Yii::$app->formatter->asCurrency($order->total_amount),
                'paid_amount' => $order->paid_amount,
                'change_amount' => $order->change_amount,
                'payment_method_id' => $order->payment_method_id,
                'payment_status' => $order->payment_status,
                'status' => $order->status,
                'note' => $order->note,
            ],
            'items' => $items,
        ];
    }

    /**
     * Apply discount code
     * @return \yii\web\Response
     */
    public function actionApplyDiscount()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $discountCode = Yii::$app->request->post('code');
        $subtotal = floatval(Yii::$app->request->post('subtotal'));
        $customerId = Yii::$app->request->post('customer_id');

        if (empty($discountCode)) {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập mã giảm giá.'
            ];
        }

        // Find discount by code
        $discount = Discount::find()
            ->where(['code' => $discountCode, 'is_active' => 1])
            ->andWhere(['<=', 'start_date', date('Y-m-d H:i:s')])
            ->andWhere(['>=', 'end_date', date('Y-m-d H:i:s')])
            ->one();

        if (!$discount) {
            return [
                'success' => false,
                'message' => 'Mã giảm giá không tồn tại hoặc đã hết hạn.'
            ];
        }

        // Check usage limit
        if ($discount->usage_limit > 0 && $discount->usage_count >= $discount->usage_limit) {
            return [
                'success' => false,
                'message' => 'Mã giảm giá đã hết lượt sử dụng.'
            ];
        }

        // Check minimum order amount
        if ($discount->min_order_amount > 0 && $subtotal < $discount->min_order_amount) {
            return [
                'success' => false,
                'message' => "Đơn hàng tối thiểu phải đạt " . Yii::$app->formatter->asCurrency($discount->min_order_amount) . " để sử dụng mã này."
            ];
        }

        // Calculate discount amount
        $discountAmount = 0;

        if ($discount->discount_type == 1) { // Percentage
            $discountAmount = $subtotal * ($discount->value / 100);

            // Apply max discount if set
            if ($discount->max_discount_amount > 0 && $discountAmount > $discount->max_discount_amount) {
                $discountAmount = $discount->max_discount_amount;
            }
        } else if ($discount->discount_type == 2) { // Fixed amount
            $discountAmount = $discount->value;
        }

        return [
            'success' => true,
            'discount' => [
                'id' => $discount->id,
                'code' => $discount->code,
                'discount_type' => $discount->discount_type,
                'value' => $discount->value,
                'discount_amount' => $discountAmount,
                'formatted_discount' => Yii::$app->formatter->asCurrency($discountAmount),
            ],
            'message' => 'Mã giảm giá đã được áp dụng.'
        ];
    }

    /**
     * Hold order
     * @return \yii\web\Response
     */
    public function actionHoldOrder()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $post = Yii::$app->request->post();
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Check if user has an active shift
            $userId = Yii::$app->user->id;
            $currentShift = Shift::find()
                ->where(['user_id' => $userId, 'status' => 0])
                ->one();

            if ($currentShift === null) {
                throw new \Exception('Bạn không có ca làm việc nào đang hoạt động.');
            }

            // Get warehouse
            $warehouseId = $post['warehouse_id'] ?? $currentShift->warehouse_id;

            // Validate cart items
            $cartItems = $post['cart_items'] ?? [];
            if (empty($cartItems)) {
                throw new \Exception('Giỏ hàng không có sản phẩm nào.');
            }

            // Check for existing hold order ID
            $holdOrderId = $post['hold_order_id'] ?? null;

            if ($holdOrderId) {
                // Update existing hold order
                $order = Order::findOne($holdOrderId);

                if (!$order || $order->status != 0) {
                    throw new \Exception('Đơn hàng tạm giữ không tồn tại hoặc đã được xử lý.');
                }

                // Delete existing order details
                OrderDetail::deleteAll(['order_id' => $order->id]);
            } else {
                // Generate order code
                $lastOrder = Order::find()
                    ->orderBy(['id' => SORT_DESC])
                    ->one();

                $orderCode = 'DH00001';
                if ($lastOrder) {
                    $lastCode = (int)substr($lastOrder->code, 2);
                    $orderCode = 'DH' . str_pad($lastCode + 1, 5, '0', STR_PAD_LEFT);
                }

                // Create new hold order
                $order = new Order();
                $order->code = $orderCode;
                $order->customer_id = $post['customer_id'] ?? null;
                $order->user_id = $userId;
                $order->shift_id = $currentShift->id;
                $order->warehouse_id = $warehouseId;
                $order->order_date = date('Y-m-d H:i:s');
                $order->status = 0; // 0 = draft (hold)
                $order->created_at = date('Y-m-d H:i:s');
                $order->updated_at = date('Y-m-d H:i:s');
            }

            // Update order data
            $order->total_quantity = $post['total_quantity'] ?? 0;
            $order->subtotal = $post['subtotal'] ?? 0;
            $order->discount_amount = $post['discount_amount'] ?? 0;
            $order->tax_amount = $post['tax_amount'] ?? 0;
            $order->total_amount = $post['total_amount'] ?? 0;
            $order->note = $post['note'] ?? null;
            $order->updated_at = date('Y-m-d H:i:s');

            if (!$order->save()) {
                throw new \Exception('Không thể tạo đơn hàng tạm giữ: ' . Json::encode($order->errors));
            }

            // Process cart items
            foreach ($cartItems as $item) {
                $product = Product::findOne($item['product_id']);
                if ($product === null) {
                    throw new \Exception('Sản phẩm không tồn tại: ' . $item['product_id']);
                }

                // Create order detail
                $orderDetail = new OrderDetail();
                $orderDetail->order_id = $order->id;
                $orderDetail->product_id = $product->id;
                $orderDetail->quantity = $item['quantity'];
                $orderDetail->unit_id = $product->unit_id;
                $orderDetail->unit_price = $item['price'];
                $orderDetail->discount_percent = $item['discount_percent'] ?? 0;
                $orderDetail->discount_amount = $item['discount_amount'] ?? 0;
                $orderDetail->tax_percent = $item['tax_percent'] ?? 0;
                $orderDetail->tax_amount = $item['tax_amount'] ?? 0;
                $orderDetail->total_amount = $item['total'];
                $orderDetail->cost_price = $product->cost_price;

                if (!$orderDetail->save()) {
                    throw new \Exception('Không thể tạo chi tiết đơn hàng: ' . Json::encode($orderDetail->errors));
                }
            }

            $transaction->commit();

            return [
                'success' => true,
                'message' => 'Đơn hàng đã được tạm giữ thành công.',
                'order' => [
                    'id' => $order->id,
                    'code' => $order->code,
                ]
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete hold order
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionDeleteHoldOrder($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $order = Order::findOne($id);

        if (!$order || $order->status != 0) {
            return [
                'success' => false,
                'message' => 'Đơn hàng tạm giữ không tồn tại hoặc đã được xử lý.'
            ];
        }

        // Check if current user is the owner
        if ($order->user_id != Yii::$app->user->id) {
            return [
                'success' => false,
                'message' => 'Bạn không có quyền xóa đơn hàng này.'
            ];
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Delete order details
            OrderDetail::deleteAll(['order_id' => $order->id]);

            // Delete order
            $order->delete();

            $transaction->commit();

            return [
                'success' => true,
                'message' => 'Đơn hàng tạm giữ đã được xóa.'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();

            return [
                'success' => false,
                'message' => 'Không thể xóa đơn hàng: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Print receipt
     * @param int $id Order ID
     * @return mixed
     */
    public function actionPrintReceipt($id)
    {
        $order = Order::findOne($id);

        if (!$order) {
            Yii::$app->session->setFlash('error', 'Đơn hàng không tồn tại.');
            return $this->redirect(['index']);
        }

        // Get order details
        $orderDetails = OrderDetail::find()
            ->where(['order_id' => $order->id])
            ->all();

        // Get store information from settings
        $storeName = Yii::$app->params['storeName'] ?? Yii::$app->name;
        $storeAddress = Yii::$app->params['storeAddress'] ?? '';
        $storePhone = Yii::$app->params['storePhone'] ?? '';
        $storeTaxCode = Yii::$app->params['storeTaxCode'] ?? '';

        return $this->renderPartial('print-receipt', [
            'order' => $order,
            'orderDetails' => $orderDetails,
            'storeName' => $storeName,
            'storeAddress' => $storeAddress,
            'storePhone' => $storePhone,
            'storeTaxCode' => $storeTaxCode,
        ]);
    }

    /**
     * Print warranty
     * @param int $id Order ID
     * @return mixed
     */
    public function actionPrintWarranty($id)
    {
        $order = Order::findOne($id);

        if (!$order) {
            Yii::$app->session->setFlash('error', 'Đơn hàng không tồn tại.');
            return $this->redirect(['index']);
        }

        // Get warranties for this order
        $warranties = Warranty::find()
            ->where(['order_id' => $order->id])
            ->all();

        if (empty($warranties)) {
            Yii::$app->session->setFlash('warning', 'Đơn hàng này không có sản phẩm bảo hành.');
            return $this->redirect(['index']);
        }

        // Get store information from settings
        $storeName = Yii::$app->params['storeName'] ?? Yii::$app->name;
        $storeAddress = Yii::$app->params['storeAddress'] ?? '';
        $storePhone = Yii::$app->params['storePhone'] ?? '';

        return $this->renderPartial('print-warranty', [
            'order' => $order,
            'warranties' => $warranties,
            'storeName' => $storeName,
            'storeAddress' => $storeAddress,
            'storePhone' => $storePhone,
        ]);
    }

    /**
     * Generate warranty for order products
     * @param Order $order
     * @return bool
     */
    private function generateWarranty($order)
    {
        // Find order details that have products with warranty period
        $orderDetails = OrderDetail::find()
            ->innerJoin('product', 'product.id = order_detail.product_id')
            ->where(['order_detail.order_id' => $order->id])
            ->andWhere(['>', 'product.warranty_period', 0])
            ->all();

        if (empty($orderDetails)) {
            return true; // No products with warranty
        }

        foreach ($orderDetails as $detail) {
            $product = Product::findOne($detail->product_id);

            if (!$product || $product->warranty_period <= 0) {
                continue;
            }

            // Generate warranty code
            $lastWarranty = Warranty::find()
                ->orderBy(['id' => SORT_DESC])
                ->one();

            $warrantyCode = 'BH00001';
            if ($lastWarranty) {
                $lastCode = (int)substr($lastWarranty->code, 2);
                $warrantyCode = 'BH' . str_pad($lastCode + 1, 5, '0', STR_PAD_LEFT);
            }

            // Create warranty record
            $warranty = new Warranty();
            $warranty->code = $warrantyCode;
            $warranty->order_id = $order->id;
            $warranty->order_detail_id = $detail->id;
            $warranty->product_id = $product->id;
            $warranty->customer_id = $order->customer_id;
            $warranty->start_date = date('Y-m-d');
            $warranty->end_date = date('Y-m-d', strtotime("+{$product->warranty_period} months"));
            $warranty->status_id = 1; // Default status (active)
            $warranty->active = 1;
            $warranty->created_at = date('Y-m-d H:i:s');
            $warranty->updated_at = date('Y-m-d H:i:s');
            $warranty->created_by = Yii::$app->user->id;

            if (!$warranty->save()) {
                Yii::error('Could not create warranty: ' . Json::encode($warranty->errors));
                return false;
            }
        }

        return true;
    }

    /**
     * Process customer points for order
     * @param Order $order
     * @param float $subtotal
     * @return bool
     */
    private function processCustomerPoints($order, $subtotal)
    {
        if (!$order->customer_id) {
            return true; // No customer associated with order
        }

        $customer = Customer::findOne($order->customer_id);
        if (!$customer) {
            return false;
        }

        // Get point settings from system
        $pointsPerAmount = Yii::$app->params['pointsPerAmount'] ?? 100000; // Default: 1 point per 100,000 VND
        $pointValue = Yii::$app->params['pointValue'] ?? 1000; // Default: 1 point = 1,000 VND

        // Calculate points earned (if any)
        if ($order->points_earned > 0) {
            // Find or create customer_point record
            $customerPoint = CustomerPoint::findOne(['customer_id' => $customer->id]);

            if (!$customerPoint) {
                $customerPoint = new CustomerPoint();
                $customerPoint->customer_id = $customer->id;
                $customerPoint->points = 0;
                $customerPoint->total_points_earned = 0;
                $customerPoint->total_points_used = 0;
            }

            // Update points
            $customerPoint->points += $order->points_earned;
            $customerPoint->total_points_earned += $order->points_earned;
            $customerPoint->updated_at = date('Y-m-d H:i:s');

            if (!$customerPoint->save()) {
                Yii::error('Could not update customer points: ' . Json::encode($customerPoint->errors));
                return false;
            }

            // Create point history record for earned points
            $pointHistory = new CustomerPointHistory();
            $pointHistory->customer_id = $customer->id;
            $pointHistory->reference_id = $order->id;
            $pointHistory->reference_type = 'order';
            $pointHistory->points = $order->points_earned;
            $pointHistory->balance = $customerPoint->points;
            $pointHistory->type = 1; // 1: add points
            $pointHistory->note = "Tích điểm từ đơn hàng {$order->code}";
            $pointHistory->created_at = date('Y-m-d H:i:s');
            $pointHistory->created_by = Yii::$app->user->id;

            if (!$pointHistory->save()) {
                Yii::error('Could not create point history: ' . Json::encode($pointHistory->errors));
                return false;
            }
        }

        // Process used points (if any)
        if ($order->points_used > 0) {
            $customerPoint = CustomerPoint::findOne(['customer_id' => $customer->id]);

            if (!$customerPoint || $customerPoint->points < $order->points_used) {
                Yii::error('Not enough points to use');
                return false;
            }

            // Update points
            $customerPoint->points -= $order->points_used;
            $customerPoint->total_points_used += $order->points_used;
            $customerPoint->updated_at = date('Y-m-d H:i:s');

            if (!$customerPoint->save()) {
                Yii::error('Could not update customer points: ' . Json::encode($customerPoint->errors));
                return false;
            }

            // Create point history record for used points
            $pointHistory = new CustomerPointHistory();
            $pointHistory->customer_id = $customer->id;
            $pointHistory->reference_id = $order->id;
            $pointHistory->reference_type = 'order';
            $pointHistory->points = $order->points_used;
            $pointHistory->balance = $customerPoint->points;
            $pointHistory->type = 2; // 2: deduct points
            $pointHistory->note = "Sử dụng điểm cho đơn hàng {$order->code}";
            $pointHistory->created_at = date('Y-m-d H:i:s');
            $pointHistory->created_by = Yii::$app->user->id;

            if (!$pointHistory->save()) {
                Yii::error('Could not create point history: ' . Json::encode($pointHistory->errors));
                return false;
            }
        }

        return true;
    }

    /**
     * Process batch products and FIFO inventory
     * @param int $productId
     * @param int $warehouseId
     * @param int $quantity
     * @return array|false
     */
    private function processBatchFifo($productId, $warehouseId, $quantity)
    {
        // Get product batches ordered by expiry date (FIFO)
        $batches = ProductBatch::find()
            ->where([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
            ])
            ->andWhere(['>', 'quantity', 0])
            ->orderBy(['expiry_date' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        if (empty($batches)) {
            return false;
        }

        $remainingQuantity = $quantity;
        $batchDetails = [];

        foreach ($batches as $batch) {
            if ($remainingQuantity <= 0) {
                break;
            }

            $takeFromBatch = min($batch->quantity, $remainingQuantity);

            $batchDetails[] = [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'quantity' => $takeFromBatch,
                'expiry_date' => $batch->expiry_date,
                'cost_price' => $batch->cost_price,
            ];

            // Update batch quantity
            $batch->quantity -= $takeFromBatch;

            if (!$batch->save()) {
                Yii::error('Could not update batch: ' . Json::encode($batch->errors));
                return false;
            }

            $remainingQuantity -= $takeFromBatch;
        }

        // If we still have remaining quantity, it means we don't have enough in batches
        if ($remainingQuantity > 0) {
            Yii::error("Not enough quantity in batches for product {$productId}");
            return false;
        }

        return $batchDetails;
    }
}
