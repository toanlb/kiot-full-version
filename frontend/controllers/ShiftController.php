<?php

namespace frontend\controllers;

use Yii;
use common\models\Shift;
use common\models\ShiftDetail;
use common\models\PaymentMethod;
use common\models\Order;
use common\models\LoginHistory;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * ShiftController implements the CRUD actions for Shift model.
 */
class ShiftController extends Controller
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
                        'actions' => ['index', 'view', 'open', 'close', 'detail', 'report'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Shift models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Shift::find()->where(['user_id' => Yii::$app->user->id]),
            'sort' => [
                'defaultOrder' => [
                    'start_time' => SORT_DESC,
                ]
            ],
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        // Check if user has active shift
        $activeShift = Shift::findActive(Yii::$app->user->identity->warehouse_id, Yii::$app->user->id);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'activeShift' => $activeShift,
        ]);
    }

    /**
     * Displays a single Shift model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        // Check if user has permission to view this shift
        if ($model->user_id != Yii::$app->user->id && !Yii::$app->user->can('manageShifts')) {
            throw new \yii\web\ForbiddenHttpException('Bạn không có quyền xem ca làm việc này.');
        }
        
        $detailsProvider = new ActiveDataProvider([
            'query' => ShiftDetail::find()->where(['shift_id' => $id]),
            'pagination' => false,
            'sort' => [
                'defaultOrder' => [
                    'transaction_type' => SORT_ASC,
                ]
            ],
        ]);

        return $this->render('view', [
            'model' => $model,
            'detailsProvider' => $detailsProvider,
        ]);
    }

    /**
     * Open a new shift.
     * @return mixed
     */
    public function actionOpen()
    {
        // Check if user has a warehouse assigned
        if (!Yii::$app->user->identity->warehouse_id) {
            Yii::$app->session->setFlash('error', 'Bạn chưa được gán vào kho hàng nào. Vui lòng liên hệ quản trị viên.');
            return $this->redirect(['site/index']);
        }
        
        // Check if user already has an open shift
        $activeShift = Shift::findActive(Yii::$app->user->identity->warehouse_id, Yii::$app->user->id);
            
        if ($activeShift) {
            Yii::$app->session->setFlash('error', 'Bạn đã có ca làm việc đang mở! Vui lòng đóng ca trước khi mở ca mới.');
            return $this->redirect(['view', 'id' => $activeShift->id]);
        }

        $model = new Shift();
        $model->user_id = Yii::$app->user->id;
        $model->warehouse_id = Yii::$app->user->identity->warehouse_id;
        $model->start_time = new Expression('NOW()');
        $model->status = Shift::STATUS_OPEN;
        $model->total_sales = 0;
        $model->total_returns = 0;
        $model->total_receipts = 0;
        $model->total_payments = 0;
        $model->expected_amount = 0;
        $model->difference = 0;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Ca làm việc đã được mở thành công.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('open', [
            'model' => $model,
        ]);
    }

    /**
     * Close an existing shift.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionClose($id)
    {
        $model = $this->findModel($id);
        
        // Check if user has permission to close this shift
        if ($model->user_id != Yii::$app->user->id && !Yii::$app->user->can('manageShifts')) {
            throw new \yii\web\ForbiddenHttpException('Bạn không có quyền đóng ca làm việc này.');
        }
        
        // Check if shift is already closed
        if ($model->status == Shift::STATUS_CLOSED) {
            Yii::$app->session->setFlash('error', 'Ca làm việc này đã được đóng.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        // Get payment methods and prepare amounts array
        $paymentMethods = PaymentMethod::find()->where(['is_active' => 1])->all();
        $paymentMethodAmounts = [];
        
        if ($model->load(Yii::$app->request->post())) {
            
            // Get payment method amounts from form
            if (isset($_POST['PaymentMethodAmount'])) {
                $paymentMethodAmounts = $_POST['PaymentMethodAmount'];
                
                // Calculate actual_amount as sum of payment method amounts
                $model->actual_amount = array_sum($paymentMethodAmounts);
            }
            
            // Set cashier_id (the user who closes the shift)
            $model->cashier_id = Yii::$app->user->id;
            $model->end_time = new Expression('NOW()');
            $model->status = Shift::STATUS_CLOSED;
            
            // Calculate difference
            $model->difference = $model->actual_amount - $model->expected_amount;
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Ca làm việc đã được đóng thành công.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('close', [
            'model' => $model,
            'paymentMethods' => $paymentMethods,
            'paymentMethodAmounts' => $paymentMethodAmounts,
        ]);
    }

    /**
     * Show shift details and transactions.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDetail($id)
    {
        $model = $this->findModel($id);
        
        // Check if user has permission to view this shift details
        if ($model->user_id != Yii::$app->user->id && !Yii::$app->user->can('manageShifts')) {
            throw new \yii\web\ForbiddenHttpException('Bạn không có quyền xem chi tiết ca làm việc này.');
        }
        
        // Get orders in this shift
        $ordersProvider = new ActiveDataProvider([
            'query' => Order::find()->where(['shift_id' => $id]),
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ],
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        
        // Get summary by payment method
        $summaryByPaymentMethod = $this->getSummaryByPaymentMethod($id);

        return $this->render('detail', [
            'model' => $model,
            'ordersProvider' => $ordersProvider,
            'summaryByPaymentMethod' => $summaryByPaymentMethod,
        ]);
    }

    /**
     * View shift report.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionReport($id)
    {
        $model = $this->findModel($id);
        
        // Check if user has permission to view this shift report
        if ($model->user_id != Yii::$app->user->id && !Yii::$app->user->can('manageShifts')) {
            throw new \yii\web\ForbiddenHttpException('Bạn không có quyền xem báo cáo ca làm việc này.');
        }
        
        // Get shift details grouped by transaction type
        $shiftDetails = ShiftDetail::find()
            ->where(['shift_id' => $id])
            ->all();
            
        // Group by transaction type
        $detailsByType = [];
        foreach ($shiftDetails as $detail) {
            if (!isset($detailsByType[$detail->transaction_type])) {
                $detailsByType[$detail->transaction_type] = [];
            }
            $detailsByType[$detail->transaction_type][] = $detail;
        }
        
        // Get summary by payment method
        $summaryByPaymentMethod = $this->getSummaryByPaymentMethod($id);
        
        // Get top-selling products
        $topProducts = Yii::$app->db->createCommand("
            SELECT p.name, p.code, SUM(od.quantity) as total_qty, SUM(od.total_amount) as total_amount
            FROM order_detail od
            JOIN `order` o ON od.order_id = o.id
            JOIN product p ON od.product_id = p.id
            WHERE o.shift_id = :shift_id
            GROUP BY p.id
            ORDER BY total_amount DESC
            LIMIT 10
        ")->bindValue(':shift_id', $id)->queryAll();

        return $this->render('report', [
            'model' => $model,
            'detailsByType' => $detailsByType,
            'summaryByPaymentMethod' => $summaryByPaymentMethod,
            'topProducts' => $topProducts,
        ]);
    }

    /**
     * Get summary by payment method for a shift
     * 
     * @param integer $shiftId
     * @return array
     */
    protected function getSummaryByPaymentMethod($shiftId)
    {
        // Get summary by payment method using SQL to ensure consistency
        $query = Yii::$app->db->createCommand("
            SELECT 
                pm.name as payment_method, 
                sd.transaction_type, 
                SUM(sd.total_amount) as total, 
                COUNT(sd.id) as count
            FROM shift_detail sd
            JOIN payment_method pm ON sd.payment_method_id = pm.id
            WHERE sd.shift_id = :shift_id
            GROUP BY pm.id, sd.transaction_type
            ORDER BY pm.name, sd.transaction_type
        ")->bindValue(':shift_id', $shiftId);
        
        $results = $query->queryAll();
        
        // Format the results as a structure for easier use in views
        $summary = [];
        foreach ($results as $row) {
            $paymentMethod = $row['payment_method'];
            
            if (!isset($summary[$paymentMethod])) {
                $summary[$paymentMethod] = [
                    'payment_method' => $paymentMethod,
                    'amounts' => [
                        1 => 0, // Sales
                        2 => 0, // Returns
                        3 => 0, // Receipts
                        4 => 0, // Payments
                    ],
                ];
            }
            
            $summary[$paymentMethod]['amounts'][$row['transaction_type']] = $row['total'];
        }
        
        return array_values($summary); // Convert to indexed array for easier iteration in views
    }

    /**
     * Finds the Shift model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Shift the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Shift::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}