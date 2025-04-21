<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use common\components\AccessControl; 
use common\models\LoginForm;
use common\models\Order;
use common\models\Product;
use common\models\Customer;
use common\models\Stock;
use common\models\Warranty;
use common\models\Payment;
use common\models\LoginHistory;
use common\models\User;
use yii\web\Response;


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
                        'actions' => ['logout', 'index', 'dashboard-data'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                   'delete'=> ['post'],
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
     * Hiển thị trang dashboard
     *
     * @return string
     */
    public function actionIndex()
    {
        $this->layout = 'main';
        
        // Lấy ngày hiện tại
        $today = date('Y-m-d');
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');
        
        // Đếm số đơn hàng hôm nay
        $todayOrders = Order::find()
            ->where(['>=', 'order_date', $today . ' 00:00:00'])
            ->andWhere(['<=', 'order_date', $today . ' 23:59:59'])
            ->count();
        
        // Tính tổng doanh thu hôm nay
        $todayRevenue = Order::find()
            ->where(['>=', 'order_date', $today . ' 00:00:00'])
            ->andWhere(['<=', 'order_date', $today . ' 23:59:59'])
            ->andWhere(['>=', 'status', 1]) // Đơn hàng đã xác nhận trở lên
            ->sum('total_amount');
            
        // Đếm số khách hàng mới hôm nay
        $newCustomers = Customer::find()
            ->where(['>=', 'created_at', $today . ' 00:00:00'])
            ->andWhere(['<=', 'created_at', $today . ' 23:59:59'])
            ->count();
            
        // Đếm số sản phẩm sắp hết hàng
        $lowStockProducts = Stock::find()
            ->joinWith(['product'])
            ->where('stock.quantity <= product.min_stock')
            ->andWhere(['product.status' => 1]) // Chỉ lấy sản phẩm đang bán
            ->count();
            
        // Lấy dữ liệu cho biểu đồ doanh thu
        $revenueData = $this->getMonthlyRevenueData();
        
        // Lấy top 5 sản phẩm bán chạy
        $topProducts = $this->getTopSellingProducts();
        
        // Lấy 5 đơn hàng gần nhất
        $recentOrders = Order::find()
            ->with(['customer']) // Eager loading để tối ưu hiệu suất
            ->orderBy(['order_date' => SORT_DESC])
            ->limit(5)
            ->all();
            
        // Lấy danh sách sản phẩm sắp hết hàng
        $lowStockProductsList = Stock::find()
            ->joinWith(['product', 'warehouse'])
            ->where('stock.quantity <= product.min_stock')
            ->andWhere(['product.status' => 1])
            ->orderBy(['(product.min_stock - stock.quantity)' => SORT_DESC]) // Sắp xếp theo mức độ thiếu hàng
            ->limit(5)
            ->all();
            
        // Lấy phiếu bảo hành mới
        $newWarranties = Warranty::find()
            ->with(['customer', 'product', 'status'])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(5)
            ->all();
            
        // Tính tổng chi phí trong tháng
        $monthlyExpenses = Payment::find()
            ->where(['>=', 'payment_date', $startOfMonth . ' 00:00:00'])
            ->andWhere(['<=', 'payment_date', $endOfMonth . ' 23:59:59'])
            ->andWhere(['status' => 1]) // Các phiếu chi đã xác nhận
            ->sum('amount');
            
        // Tính tổng doanh thu trong tháng
        $monthlyRevenue = Order::find()
            ->where(['>=', 'order_date', $startOfMonth . ' 00:00:00'])
            ->andWhere(['<=', 'order_date', $endOfMonth . ' 23:59:59'])
            ->andWhere(['>=', 'status', 1]) // Đơn hàng đã xác nhận trở lên
            ->sum('total_amount');
            
        // Tính lợi nhuận
        $monthlyProfit = $monthlyRevenue - $monthlyExpenses;
        
        // Tính tỷ suất lợi nhuận
        $profitMargin = $monthlyRevenue > 0 ? ($monthlyProfit / $monthlyRevenue * 100) : 0;
        
        return $this->render('index', [
            'todayOrders' => $todayOrders ?: 0,
            'todayRevenue' => $todayRevenue ?: 0,
            'newCustomers' => $newCustomers ?: 0,
            'lowStockProducts' => $lowStockProducts ?: 0,
            'revenueData' => $revenueData,
            'topProducts' => $topProducts,
            'recentOrders' => $recentOrders,
            'lowStockProductsList' => $lowStockProductsList,
            'newWarranties' => $newWarranties,
            'monthlyRevenue' => $monthlyRevenue ?: 0,
            'monthlyExpenses' => $monthlyExpenses ?: 0,
            'monthlyProfit' => $monthlyProfit ?: 0,
            'profitMargin' => $profitMargin,
        ]);
    }

    /**
     * Lấy dữ liệu doanh thu theo tháng cho biểu đồ
     * 
     * @return array
     */
    private function getMonthlyRevenueData()
    {
        $currentMonth = date('m');
        $currentYear = date('Y');
        $days = date('t'); // Số ngày trong tháng hiện tại
        
        $result = [
            'labels' => [],
            'revenue' => [],
            'profit' => []
        ];
        
        // Chia tháng thành 6 khoảng, mỗi khoảng 5 ngày
        $interval = ceil($days / 6);
        
        for ($i = 1; $i <= $days; $i += $interval) {
            $startDay = $i;
            $endDay = min($i + $interval - 1, $days);
            
            $startDate = "$currentYear-$currentMonth-$startDay 00:00:00";
            $endDate = "$currentYear-$currentMonth-$endDay 23:59:59";
            
            // Tính doanh thu trong khoảng thời gian
            $revenue = Order::find()
                ->where(['>=', 'order_date', $startDate])
                ->andWhere(['<=', 'order_date', $endDate])
                ->andWhere(['>=', 'status', 1])
                ->sum('total_amount') ?: 0;
                
            // Tính chi phí trong khoảng thời gian 
            $expenses = Payment::find()
                ->where(['>=', 'payment_date', $startDate])
                ->andWhere(['<=', 'payment_date', $endDate])
                ->andWhere(['status' => 1])
                ->sum('amount') ?: 0;
                
            // Tính lợi nhuận
            $profit = $revenue - $expenses;
            
            $result['labels'][] = "$startDay/$currentMonth";
            $result['revenue'][] = $revenue;
            $result['profit'][] = $profit;
        }
        
        return $result;
    }
    
    /**
     * Lấy danh sách sản phẩm bán chạy nhất
     * 
     * @return array
     */
    private function getTopSellingProducts()
    {
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');
        
        // Sử dụng câu lệnh SQL để lấy sản phẩm bán chạy nhất
        $sql = "SELECT p.id, p.name, p.code, SUM(od.quantity) as total_quantity
                FROM order_detail od
                JOIN `order` o ON od.order_id = o.id
                JOIN product p ON od.product_id = p.id
                WHERE o.order_date BETWEEN :start_date AND :end_date
                AND o.status >= 1
                GROUP BY p.id, p.name, p.code
                ORDER BY total_quantity DESC
                LIMIT 5";
                
        $params = [
            ':start_date' => $startOfMonth . ' 00:00:00',
            ':end_date' => $endOfMonth . ' 23:59:59',
        ];
        
        return Yii::$app->db->createCommand($sql)
            ->bindValues($params)
            ->queryAll();
    }

    /**
     * API để lấy dữ liệu dashboard thời gian thực
     * 
     * @return \yii\web\Response
     */
    public function actionDashboardData()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $today = date('Y-m-d');
        
        // Đếm số đơn hàng hôm nay
        $todayOrders = Order::find()
            ->where(['>=', 'order_date', $today . ' 00:00:00'])
            ->andWhere(['<=', 'order_date', $today . ' 23:59:59'])
            ->count();
        
        // Tính tổng doanh thu hôm nay
        $todayRevenue = Order::find()
            ->where(['>=', 'order_date', $today . ' 00:00:00'])
            ->andWhere(['<=', 'order_date', $today . ' 23:59:59'])
            ->andWhere(['>=', 'status', 1]) // Đơn hàng đã xác nhận trở lên
            ->sum('total_amount');
            
        // Đếm số khách hàng mới hôm nay
        $newCustomers = Customer::find()
            ->where(['>=', 'created_at', $today . ' 00:00:00'])
            ->andWhere(['<=', 'created_at', $today . ' 23:59:59'])
            ->count();
            
        // Đếm số sản phẩm sắp hết hàng
        $lowStockProducts = Stock::find()
            ->joinWith(['product'])
            ->where('stock.quantity <= product.min_stock')
            ->andWhere(['product.status' => 1]) // Chỉ lấy sản phẩm đang bán
            ->count();
        
        return [
            'todayOrders' => $todayOrders ?: 0,
            'todayRevenue' => $todayRevenue ?: 0,
            'newCustomers' => $newCustomers ?: 0,
            'lowStockProducts' => $lowStockProducts ?: 0,
            'serverTime' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'blank'; // Sử dụng layout blank cho trang login

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            // Ghi log đăng nhập
            $this->saveLoginHistory();
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Lưu lịch sử đăng nhập
     */
    private function saveLoginHistory()
    {
        $loginHistory = new LoginHistory();
        $loginHistory->user_id = Yii::$app->user->id;
        $loginHistory->login_time = date('Y-m-d H:i:s');
        $loginHistory->ip_address = Yii::$app->request->userIP;
        $loginHistory->user_agent = Yii::$app->request->userAgent;
        $loginHistory->success = 1;
        $loginHistory->save();
        
        // Cập nhật thời gian đăng nhập cuối cho user
        $user = User::findOne(Yii::$app->user->id);
        if ($user) {
            $user->last_login_at = date('Y-m-d H:i:s');
            $user->save(false); // Skip validation
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        // Cập nhật thời gian đăng xuất trong lịch sử đăng nhập
        $loginHistory = LoginHistory::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->andWhere(['IS', 'logout_time', null])
            ->orderBy(['login_time' => SORT_DESC])
            ->one();
            
        if ($loginHistory) {
            $loginHistory->logout_time = date('Y-m-d H:i:s');
            $loginHistory->save();
        }
        
        Yii::$app->user->logout();

        return $this->goHome();
    }
}