<?php

namespace backend\controllers;

use Yii;
use common\models\Order;
use common\models\OrderDetail;
use common\models\OrderPayment;
use common\models\Customer;
use common\models\Product;
use common\models\PaymentMethod;
use common\models\User;
use common\models\Warehouse;
use common\models\OrderSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\components\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\mpdf\Pdf;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class OrderController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'change-status', 'print-invoice'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('manageOrders');
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'change-status' => ['POST'],
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

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'statusList' => $this->getStatusList(),
            'paymentStatusList' => $this->getPaymentStatusList(),
            'shippingStatusList' => $this->getShippingStatusList(),
            'paymentMethods' => ArrayHelper::map(PaymentMethod::find()->where(['is_active' => 1])->all(), 'id', 'name'),
            'warehouses' => ArrayHelper::map(Warehouse::find()->where(['is_active' => 1])->all(), 'id', 'name'),
        ]);
    }

    /**
     * Displays a single Order model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $orderDetails = OrderDetail::find()->where(['order_id' => $id])->all();
        $orderPayments = OrderPayment::find()->where(['order_id' => $id])->all();
        
        $totalPaid = 0;
        foreach ($orderPayments as $payment) {
            if ($payment->status == 1) { // Trạng thái thanh toán thành công
                $totalPaid += $payment->amount;
            }
        }

        return $this->render('view', [
            'model' => $model,
            'orderDetails' => $orderDetails,
            'orderPayments' => $orderPayments,
            'totalPaid' => $totalPaid,
            'statusList' => $this->getStatusList(),
            'paymentStatusList' => $this->getPaymentStatusList(),
            'shippingStatusList' => $this->getShippingStatusList(),
            'paymentMethods' => ArrayHelper::map(PaymentMethod::find()->where(['is_active' => 1])->all(), 'id', 'name'),
        ]);
    }

    /**
     * Changes status of an Order model.
     * @param integer $id
     * @param integer $status
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionChangeStatus($id, $status)
    {
        $model = $this->findModel($id);
        $oldStatus = $model->status;
        
        // Kiểm tra trạng thái hợp lệ
        $validStatuses = array_keys($this->getStatusList());
        if (!in_array($status, $validStatuses)) {
            Yii::$app->session->setFlash('error', 'Trạng thái không hợp lệ');
            return $this->redirect(['view', 'id' => $id]);
        }

        // Validate chuyển trạng thái
        if ($oldStatus == 5) { // Đơn đã hủy không thể chuyển sang trạng thái khác
            Yii::$app->session->setFlash('error', 'Không thể thay đổi trạng thái của đơn hàng đã hủy');
            return $this->redirect(['view', 'id' => $id]);
        }

        if ($status == 5 && $oldStatus >= 3) { // Không thể hủy đơn hàng đã giao hoặc hoàn thành
            Yii::$app->session->setFlash('error', 'Không thể hủy đơn hàng đã giao hoặc hoàn thành');
            return $this->redirect(['view', 'id' => $id]);
        }

        // Cập nhật trạng thái
        $model->status = $status;
        
        // Cập nhật các trạng thái liên quan
        if ($status == 2) { // Đã thanh toán
            $model->payment_status = 2; // Paid
        } elseif ($status == 3) { // Đã giao hàng
            $model->shipping_status = 2; // Delivered
            if (!$model->delivery_date) {
                $model->delivery_date = date('Y-m-d H:i:s');
            }
        } elseif ($status == 5) { // Hủy đơn
            $this->cancelOrder($model);
        }

        if ($model->save()) {
            // Ghi log thay đổi trạng thái
            $this->logStatusChange($model, $oldStatus, $status);
            
            Yii::$app->session->setFlash('success', 'Cập nhật trạng thái đơn hàng thành công');
        } else {
            Yii::$app->session->setFlash('error', 'Có lỗi khi cập nhật trạng thái đơn hàng');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Print invoice for an Order.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionPrintInvoice($id)
    {
        $model = $this->findModel($id);
        $orderDetails = OrderDetail::find()->where(['order_id' => $id])->all();
        $orderPayments = OrderPayment::find()->where(['order_id' => $id])->all();
        
        // Tạo PDF
        $content = $this->renderPartial('_invoice', [
            'model' => $model,
            'orderDetails' => $orderDetails,
            'orderPayments' => $orderPayments,
        ]);

        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8,
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'destination' => Pdf::DEST_BROWSER,
            'content' => $content,
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
            'cssInline' => '.kv-heading-1{font-size:18px}',
            'options' => ['title' => 'Hóa đơn #' . $model->code],
            'methods' => [
                'SetHeader' => ['Hóa đơn #' . $model->code . ' | KIOT POS | Ngày in: ' . date('d/m/Y')],
                'SetFooter' => ['{PAGENO}'],
            ]
        ]);

        return $pdf->render();
    }

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Order::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Đơn hàng không tồn tại');
    }

    /**
     * Get list of order statuses
     * @return array
     */
    protected function getStatusList()
    {
        return [
            0 => 'Nháp',
            1 => 'Đã xác nhận',
            2 => 'Đã thanh toán',
            3 => 'Đã giao hàng',
            4 => 'Hoàn thành',
            5 => 'Đã hủy',
        ];
    }

    /**
     * Get list of payment statuses
     * @return array
     */
    protected function getPaymentStatusList()
    {
        return [
            0 => 'Chưa thanh toán',
            1 => 'Thanh toán một phần',
            2 => 'Đã thanh toán',
        ];
    }

    /**
     * Get list of shipping statuses
     * @return array
     */
    protected function getShippingStatusList()
    {
        return [
            0 => 'Chưa giao hàng',
            1 => 'Đang giao hàng',
            2 => 'Đã giao hàng',
        ];
    }

    /**
     * Log status change
     * @param Order $model
     * @param int $oldStatus
     * @param int $newStatus
     */
    protected function logStatusChange($model, $oldStatus, $newStatus)
    {
        $statusList = $this->getStatusList();
        $oldStatusName = isset($statusList[$oldStatus]) ? $statusList[$oldStatus] : 'Unknown';
        $newStatusName = isset($statusList[$newStatus]) ? $statusList[$newStatus] : 'Unknown';
        
        Yii::info(sprintf(
            'Đơn hàng %s (ID: %s) thay đổi trạng thái từ "%s" thành "%s" bởi %s',
            $model->code,
            $model->id,
            $oldStatusName,
            $newStatusName,
            Yii::$app->user->identity->username
        ), 'order');
    }

    /**
     * Handle order cancellation
     * @param Order $model
     */
    protected function cancelOrder($model)
    {
        // TODO: Xử lý hủy đơn hàng (khôi phục tồn kho, xóa phiếu xuất, điểm tích lũy...)
        // Đây là nơi để thêm logic xử lý việc khôi phục tồn kho khi hủy đơn hàng
        // Có thể gọi đến một service hoặc các hàm xử lý khác
    }
}