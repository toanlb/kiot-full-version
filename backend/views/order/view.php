<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\Order */
/* @var $orderDetails array */
/* @var $orderPayments array */
/* @var $totalPaid float */
/* @var $statusList array */
/* @var $paymentStatusList array */
/* @var $shippingStatusList array */
/* @var $paymentMethods array */

$this->title = 'Đơn hàng: ' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'Quản lý đơn hàng', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

// Helper function to get CSS class for status badge
function getStatusBadgeClass($status) {
    switch ($status) {
        case 0: return "secondary";
        case 1: return "primary";
        case 2: return "info";
        case 3: return "warning";
        case 4: return "success";
        case 5: return "danger";
        default: return "secondary";
    }
}

// Helper function to get CSS class for payment status badge
function getPaymentStatusBadgeClass($status) {
    switch ($status) {
        case 0: return "secondary";
        case 1: return "warning";
        case 2: return "success";
        default: return "secondary";
    }
}

// Helper function to get CSS class for shipping status badge
function getShippingStatusBadgeClass($status) {
    switch ($status) {
        case 0: return "secondary";
        case 1: return "warning";
        case 2: return "success";
        default: return "secondary";
    }
}
?>

<div class="order-view">
    <div class="row">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-print"></i> In hóa đơn', ['print-invoice', 'id' => $model->id], [
                            'class' => 'btn btn-outline-secondary btn-sm',
                            'target' => '_blank'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-arrow-left"></i> Quay lại', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">Thông tin đơn hàng</h3>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-striped">
                                        <tr>
                                            <th>Mã đơn hàng:</th>
                                            <td><?= $model->code ?></td>
                                        </tr>
                                        <tr>
                                            <th>Ngày đặt hàng:</th>
                                            <td><?= Yii::$app->formatter->asDatetime($model->order_date) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Trạng thái:</th>
                                            <td>
                                                <span class="badge badge-<?= getStatusBadgeClass($model->status) ?>">
                                                    <?= $statusList[$model->status] ?? 'Unknown' ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Trạng thái thanh toán:</th>
                                            <td>
                                                <span class="badge badge-<?= getPaymentStatusBadgeClass($model->payment_status) ?>">
                                                    <?= $paymentStatusList[$model->payment_status] ?? 'Unknown' ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Kho hàng:</th>
                                            <td><?= $model->warehouse ? $model->warehouse->name : 'N/A' ?></td>
                                        </tr>
                                        <tr>
                                            <th>Nhân viên:</th>
                                            <td><?= $model->user ? $model->user->username : 'N/A' ?></td>
                                        </tr>
                                        <?php if ($model->note): ?>
                                        <tr>
                                            <th>Ghi chú:</th>
                                            <td><?= Yii::$app->formatter->asNtext($model->note) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                                <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                        </div>
                        <!-- /.col-md-6 -->
                        <div class="col-md-6">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">Thông tin khách hàng</h3>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-striped">
                                        <tr>
                                            <th>Khách hàng:</th>
                                            <td>
                                                <?php if ($model->customer): ?>
                                                    <?= Html::a($model->customer->name, ['/customer/view', 'id' => $model->customer_id]) ?>
                                                <?php else: ?>
                                                    Khách lẻ
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php if ($model->customer): ?>
                                        <tr>
                                            <th>Số điện thoại:</th>
                                            <td><?= $model->customer->phone ?></td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td><?= $model->customer->email ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($model->shipping_address): ?>
                                        <tr>
                                            <th>Địa chỉ giao hàng:</th>
                                            <td><?= $model->shipping_address ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <th>Trạng thái giao hàng:</th>
                                            <td>
                                                <span class="badge badge-<?= getShippingStatusBadgeClass($model->shipping_status) ?>">
                                                    <?= $shippingStatusList[$model->shipping_status] ?? 'Unknown' ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php if ($model->delivery_date): ?>
                                        <tr>
                                            <th>Ngày giao hàng:</th>
                                            <td><?= Yii::$app->formatter->asDatetime($model->delivery_date) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($model->shipping_fee > 0): ?>
                                        <tr>
                                            <th>Phí vận chuyển:</th>
                                            <td><?= Yii::$app->formatter->asDecimal($model->shipping_fee) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                                <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                        </div>
                        <!-- /.col-md-6 -->
                    </div>
                    <!-- /.row -->

                    <div class="card card-outline card-primary mt-4">
                        <div class="card-header">
                            <h3 class="card-title">Chi tiết sản phẩm</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px">#</th>
                                            <th>Sản phẩm</th>
                                            <th style="width: 100px" class="text-center">Số lượng</th>
                                            <th style="width: 120px" class="text-right">Đơn giá</th>
                                            <th style="width: 100px" class="text-right">Giảm giá</th>
                                            <th style="width: 120px" class="text-right">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orderDetails as $i => $detail): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td>
                                                <?php if ($detail->product): ?>
                                                    <?= Html::a($detail->product->name, ['/product/view', 'id' => $detail->product_id]) ?>
                                                    <?php if ($detail->product->code): ?>
                                                        <small class="text-muted d-block"><?= $detail->product->code ?></small>
                                                    <?php endif; ?>
                                                    <?php if ($detail->note): ?>
                                                        <small class="text-muted d-block">Ghi chú: <?= $detail->note ?></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    Sản phẩm không tồn tại
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?= $detail->quantity ?> <?= $detail->unit ? $detail->unit->abbreviation : '' ?>
                                            </td>
                                            <td class="text-right"><?= Yii::$app->formatter->asDecimal($detail->unit_price) ?></td>
                                            <td class="text-right">
                                                <?php if ($detail->discount_amount > 0): ?>
                                                    <?= Yii::$app->formatter->asDecimal($detail->discount_amount) ?>
                                                    <?php if ($detail->discount_percent > 0): ?>
                                                        <small class="text-muted d-block">(<?= $detail->discount_percent ?>%)</small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    0
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-right"><?= Yii::$app->formatter->asDecimal($detail->total_amount) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5" class="text-right">Tổng cộng:</th>
                                            <th class="text-right"><?= Yii::$app->formatter->asDecimal($model->subtotal) ?></th>
                                        </tr>
                                        <?php if ($model->discount_amount > 0): ?>
                                        <tr>
                                            <th colspan="5" class="text-right">Giảm giá:</th>
                                            <th class="text-right"><?= Yii::$app->formatter->asDecimal($model->discount_amount) ?></th>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($model->tax_amount > 0): ?>
                                        <tr>
                                            <th colspan="5" class="text-right">Thuế:</th>
                                            <th class="text-right"><?= Yii::$app->formatter->asDecimal($model->tax_amount) ?></th>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($model->shipping_fee > 0): ?>
                                        <tr>
                                            <th colspan="5" class="text-right">Phí vận chuyển:</th>
                                            <th class="text-right"><?= Yii::$app->formatter->asDecimal($model->shipping_fee) ?></th>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <th colspan="5" class="text-right">Tổng thanh toán:</th>
                                            <th class="text-right"><?= Yii::$app->formatter->asDecimal($model->total_amount) ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->

                    <div class="card card-outline card-success mt-4">
                        <div class="card-header">
                            <h3 class="card-title">Lịch sử thanh toán</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px">#</th>
                                            <th>Ngày thanh toán</th>
                                            <th>Phương thức</th>
                                            <th>Mã tham chiếu</th>
                                            <th>Trạng thái</th>
                                            <th class="text-right">Số tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($orderPayments) > 0): ?>
                                            <?php foreach ($orderPayments as $i => $payment): ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
                                                <td><?= Yii::$app->formatter->asDatetime($payment->payment_date) ?></td>
                                                <td><?= $payment->paymentMethod ? $payment->paymentMethod->name : 'N/A' ?></td>
                                                <td><?= $payment->reference_number ?? 'N/A' ?></td>
                                                <td>
                                                    <?php if ($payment->status == 0): ?>
                                                        <span class="badge badge-warning">Đang xử lý</span>
                                                    <?php elseif ($payment->status == 1): ?>
                                                        <span class="badge badge-success">Thành công</span>
                                                    <?php elseif ($payment->status == 2): ?>
                                                        <span class="badge badge-danger">Thất bại</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-right"><?= Yii::$app->formatter->asDecimal($payment->amount) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center">Chưa có lịch sử thanh toán</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5" class="text-right">Đã thanh toán:</th>
                                            <th class="text-right"><?= Yii::$app->formatter->asDecimal($totalPaid) ?></th>
                                        </tr>
                                        <tr>
                                            <th colspan="5" class="text-right">Còn lại:</th>
                                            <th class="text-right"><?= Yii::$app->formatter->asDecimal($model->total_amount - $totalPaid) ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col-md-9 -->

        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thao tác</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="btn-group-vertical btn-block">
                        <?php if ($model->status != 5): // Nếu không phải đơn hàng đã hủy ?>
                            <?php if ($model->status < 4): // Nếu chưa hoàn thành ?>
                                <a href="#" 
                                   class="btn btn-primary change-status-btn" 
                                   data-url="<?= Url::to(['change-status', 'id' => $model->id, 'status' => $model->status + 1]) ?>"
                                   data-message="Bạn có chắc chắn muốn chuyển đơn hàng sang trạng thái tiếp theo?">
                                    <i class="fas fa-arrow-right"></i> Chuyển trạng thái tiếp theo
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($model->status < 3): // Nếu chưa giao hàng ?>
                                <a href="#" 
                                   class="btn btn-danger change-status-btn" 
                                   data-url="<?= Url::to(['change-status', 'id' => $model->id, 'status' => 5]) ?>"
                                   data-message="Bạn có chắc chắn muốn hủy đơn hàng này?">
                                    <i class="fas fa-times"></i> Hủy đơn hàng
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <a href="<?= Url::to(['print-invoice', 'id' => $model->id]) ?>" class="btn btn-default" target="_blank">
                            <i class="fas fa-print"></i> In hóa đơn
                        </a>

                        <a href="<?= Url::to(['/return/create', 'order_id' => $model->id]) ?>" class="btn btn-warning">
                            <i class="fas fa-exchange-alt"></i> Tạo đơn trả hàng
                        </a>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->

            <?php if ($model->status != 5 && $model->payment_status < 2): // Nếu không phải đơn hàng đã hủy và chưa thanh toán đủ ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thêm thanh toán</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <a href="<?= Url::to(['/order-payment/create', 'order_id' => $model->id]) ?>" class="btn btn-success btn-block">
                        <i class="fas fa-plus"></i> Thêm thanh toán
                    </a>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
            <?php endif; ?>
        </div>
        <!-- /.col-md-3 -->
    </div>
    <!-- /.row -->
</div>

<!-- Status Change Form -->
<form id="status-change-form" method="post" style="display: none;">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />
</form>

<?php
$script = <<<JS
// Handle status change buttons
$('.change-status-btn').on('click', function(e) {
    e.preventDefault();
    var url = $(this).data('url');
    var message = $(this).data('message');
    
    if (confirm(message)) {
        var form = $('#status-change-form');
        form.attr('action', url);
        form.submit();
    }
});
JS;
$this->registerJs($script);
?>