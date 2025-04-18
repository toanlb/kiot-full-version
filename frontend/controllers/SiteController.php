<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use common\models\Order;
use common\models\Shift;
use common\models\Stock;
use common\models\Product;
use common\models\OrderDetail;
use yii\db\Expression;
use common\models\LoginHistory;
/**
 * Site controller
 */
class SiteController extends Controller
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
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        // Get current user's warehouse
        $userId = Yii::$app->user->id;
        $user = Yii::$app->user->identity;
        $warehouseId = $user->warehouse_id;
        
        // Get active shift for this user in this warehouse
        $activeShift = Shift::findActive($warehouseId, $userId);
        
        // Get today's orders count and revenue
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');
        
        $todayOrdersQuery = Order::find()
            ->where(['warehouse_id' => $warehouseId])
            ->andWhere(['between', 'order_date', $todayStart, $todayEnd])
            ->andWhere(['!=', 'status', Order::STATUS_CANCELED]);
            
        $todayOrders = [
            'count' => $todayOrdersQuery->count(),
            'revenue' => $todayOrdersQuery->sum('total_amount') ?: 0,
        ];
        
        // Get top selling products for the last 30 days
        $date30DaysAgo = date('Y-m-d', strtotime('-30 days'));
        
        // Tùy chọn 1: Sử dụng ActiveRecord nếu bạn cần load model đầy đủ
        $topProductsQuery = OrderDetail::find()
            ->alias('od')
            ->select([
                'p.id', 
                'p.code', 
                'p.name', 
                'SUM(od.quantity) as quantity', 
                'SUM(od.total_amount) as revenue'
            ])
            ->innerJoin(['o' => Order::tableName()], 'od.order_id = o.id')
            ->innerJoin(['p' => Product::tableName()], 'od.product_id = p.id')
            ->where(['o.warehouse_id' => $warehouseId])
            ->andWhere(['>=', 'o.order_date', $date30DaysAgo])
            ->andWhere(['!=', 'o.status', Order::STATUS_CANCELED])
            ->groupBy(['p.id', 'p.code', 'p.name'])
            ->orderBy(['quantity' => SORT_DESC])
            ->limit(5);
            
        $topProducts = $topProductsQuery->asArray()->all();
        
        // Nếu không có dữ liệu, tạo dữ liệu mẫu
        if (empty($topProducts)) {
            $topProducts = [
                ['id' => 1, 'code' => 'P001', 'name' => 'Sản phẩm mẫu 1'],
                ['id' => 2, 'code' => 'P002', 'name' => 'Sản phẩm mẫu 2'],
                ['id' => 3, 'code' => 'P003', 'name' => 'Sản phẩm mẫu 3'],
            ];
        }
        
        // Get low stock products
        $lowStockProductsData = Stock::getLowStockProducts($warehouseId);
        $lowStockProducts = [];
        
        foreach ($lowStockProductsData as $stock) {
            $minStock = $stock->min_stock ?? $stock->product->min_stock;
            
            $lowStockProducts[] = [
                'id' => $stock->product_id,
                'code' => $stock->product->code,
                'name' => $stock->product->name,
                'quantity' => $stock->quantity,
                'min_stock' => $minStock,
            ];
        }
        
        // Get recent orders
        $recentOrders = Order::find()
            ->alias('o')
            ->select([
                'o.id', 
                'o.code', 
                'o.order_date', 
                'o.total_amount', 
                'o.payment_status', 
                'o.status',
                'c.name as customer_name'
            ])
            ->leftJoin(['c' => 'customer'], 'o.customer_id = c.id')
            ->where(['o.warehouse_id' => $warehouseId])
            ->orderBy(['o.order_date' => SORT_DESC])
            ->limit(10)
            ->asArray()
            ->all();
            
        // Nếu không có đơn hàng, tạo dữ liệu mẫu
        if (empty($recentOrders)) {
            $recentOrders = [
                [
                    'id' => 1,
                    'code' => 'ORD000001',
                    'order_date' => date('Y-m-d H:i:s'),
                    'total_amount' => 100000,
                    'payment_status' => Order::PAYMENT_STATUS_PAID,
                    'status' => Order::STATUS_COMPLETED,
                    'customer_name' => 'Khách lẻ'
                ],
                [
                    'id' => 2,
                    'code' => 'ORD000002',
                    'order_date' => date('Y-m-d H:i:s', strtotime('-1 day')),
                    'total_amount' => 200000,
                    'payment_status' => Order::PAYMENT_STATUS_PAID,
                    'status' => Order::STATUS_COMPLETED,
                    'customer_name' => 'Khách lẻ'
                ],
                [
                    'id' => 3,
                    'code' => 'ORD000003',
                    'order_date' => date('Y-m-d H:i:s', strtotime('-2 day')),
                    'total_amount' => 150000,
                    'payment_status' => Order::PAYMENT_STATUS_PAID,
                    'status' => Order::STATUS_COMPLETED,
                    'customer_name' => 'Khách lẻ'
                ]
            ];
        }
        
        return $this->render('index', [
            'activeShift' => $activeShift,
            'todayOrders' => $todayOrders,
            'topProducts' => $topProducts,
            'lowStockProducts' => $lowStockProducts,
            'recentOrders' => $recentOrders,
        ]);
    }

    /**
     * Login action.
     *
     * @return string|\yii\web\Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'login';
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        // Lưu user ID trước khi đăng xuất
        $userId = Yii::$app->user->id;
        
        Yii::$app->user->logout();
        
        // Ghi log đăng xuất nếu có user ID
        if ($userId) {
            LoginHistory::logLogout($userId);
        }

        return $this->goHome();
    }
}