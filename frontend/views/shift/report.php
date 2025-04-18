<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use dosamigos\chartjs\ChartJs;
use common\models\ShiftDetail;

/* @var $this yii\web\View */
/* @var $model common\models\Shift */
/* @var $detailsByType array */
/* @var $summaryByPaymentMethod array */
/* @var $topProducts array */

$this->title = 'Báo cáo ca làm việc #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Quản lý ca làm việc', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Ca làm việc #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Báo cáo';

// Prepare data for charts
$transactionLabels = [];
$transactionData = [];
$transactionColors = [
    ShiftDetail::TYPE_SALES => '#28a745', // Green
    ShiftDetail::TYPE_RETURNS => '#dc3545', // Red
    ShiftDetail::TYPE_RECEIPTS => '#17a2b8', // Blue
    ShiftDetail::TYPE_PAYMENTS => '#ffc107', // Yellow
];

$transactionTypes = ShiftDetail::getTransactionTypes();
foreach($transactionTypes as $type => $label) {
    $total = 0;
    if (isset($detailsByType[$type])) {
        foreach ($detailsByType[$type] as $detail) {
            $total += $detail->total_amount;
        }
    }
    
    if ($total > 0 || $type == ShiftDetail::TYPE_SALES) {  // Always include sales even if 0
        $transactionLabels[] = $label;
        $transactionData[] = $total;
    }
}

// Sales by payment method data
$paymentMethodLabels = [];
$paymentMethodData = [];
$paymentMethodColors = [
    '#4e73df', // Blue
    '#1cc88a', // Green
    '#36b9cc', // Turquoise
    '#f6c23e', // Yellow
    '#e74a3b', // Red
    '#6f42c1', // Purple
    '#5a5c69', // Gray
];

// Group by payment method for sales only
$paymentSummary = [];
foreach ($summaryByPaymentMethod as $item) {
    if ($item['transaction_type'] == ShiftDetail::TYPE_SALES) {
        $paymentMethodLabels[] = $item['name'];
        $paymentMethodData[] = $item['total'];
    }
}

// Top products data
$topProductLabels = [];
$topProductData = [];
if (!empty($topProducts)) {
    foreach ($topProducts as $product) {
        $topProductLabels[] = $product['name'];
        $topProductData[] = $product['total_amount'];
    }
}
?>
<div class="shift-report">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?= Html::a('<i class="fas fa-print"></i> In báo cáo', ['#'], [
                    'class' => 'btn btn-default btn-sm',
                    'onclick' => 'window.print(); return false;',
                ]) ?>
                <?= Html::a('<i class="fas fa-arrow-left"></i> Quay lại', ['view', 'id' => $model->id], [
                    'class' => 'btn btn-default btn-sm',
                ]) ?>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary">
                            <h3 class="card-title">Thông tin ca làm việc</h3>
                        </div>
                        <div class="card-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    [
                                        'attribute' => 'user_id',
                                        'value' => $model->user->username,
                                    ],
                                    [
                                        'attribute' => 'warehouse_id',
                                        'value' => $model->warehouse->name,
                                    ],
                                    [
                                        'attribute' => 'start_time',
                                        'format' => 'datetime',
                                    ],
                                    [
                                        'attribute' => 'end_time',
                                        'format' => 'datetime',
                                    ],
                                    [
                                        'attribute' => 'opening_amount',
                                        'format' => 'currency',
                                    ],
                                    [
                                        'attribute' => 'total_sales',
                                        'format' => 'currency',
                                    ],
                                    [
                                        'attribute' => 'total_returns',
                                        'format' => 'currency',
                                    ],
                                    [
                                        'attribute' => 'total_receipts',
                                        'format' => 'currency',
                                    ],
                                    [
                                        'attribute' => 'total_payments',
                                        'format' => 'currency',
                                    ],
                                    [
                                        'attribute' => 'expected_amount',
                                        'format' => 'currency',
                                    ],
                                    [
                                        'attribute' => 'actual_amount',
                                        'format' => 'currency',
                                    ],
                                    [
                                        'attribute' => 'difference',
                                        'format' => 'currency',
                                        'contentOptions' => function ($model) {
                                            return $model->difference < 0 ? ['class' => 'text-danger'] : 
                                                  ($model->difference > 0 ? ['class' => 'text-success'] : []);
                                        },
                                    ],
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-success">
                            <h3 class="card-title">Biểu đồ giao dịch</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($transactionData)): ?>
                                <?= ChartJs::widget([
                                    'type' => 'doughnut',
                                    'options' => [
                                        'height' => 300,
                                    ],
                                    'data' => [
                                        'labels' => $transactionLabels,
                                        'datasets' => [
                                            [
                                                'data' => $transactionData,
                                                'backgroundColor' => array_values($transactionColors),
                                            ],
                                        ],
                                    ],
                                    'clientOptions' => [
                                        'legend' => [
                                            'display' => true,
                                            'position' => 'bottom',
                                        ],
                                    ],
                                ]) ?>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    Không có dữ liệu giao dịch.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h3 class="card-title">Bán hàng theo phương thức thanh toán</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($paymentMethodData)): ?>
                                <?= ChartJs::widget([
                                    'type' => 'pie',
                                    'options' => [
                                        'height' => 300,
                                    ],
                                    'data' => [
                                        'labels' => $paymentMethodLabels,
                                        'datasets' => [
                                            [
                                                'data' => $paymentMethodData,
                                                'backgroundColor' => array_slice($paymentMethodColors, 0, count($paymentMethodData)),
                                            ],
                                        ],
                                    ],
                                    'clientOptions' => [
                                        'legend' => [
                                            'display' => true,
                                            'position' => 'bottom',
                                        ],
                                    ],
                                ]) ?>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    Không có dữ liệu bán hàng trong ca này.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-warning">
                            <h3 class="card-title">Top sản phẩm bán chạy</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($topProductData)): ?>
                                <?= ChartJs::widget([
                                    'type' => 'horizontalBar',
                                    'options' => [
                                        'height' => 300,
                                    ],
                                    'data' => [
                                        'labels' => $topProductLabels,
                                        'datasets' => [
                                            [
                                                'label' => 'Doanh thu',
                                                'data' => $topProductData,
                                                'backgroundColor' => '#4e73df',
                                            ],
                                        ],
                                    ],
                                    'clientOptions' => [
                                        'scales' => [
                                            'xAxes' => [
                                                [
                                                    'ticks' => [
                                                        'beginAtZero' => true,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ]) ?>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    Không có dữ liệu sản phẩm bán ra trong ca này.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h3 class="card-title">Chi tiết theo phương thức thanh toán</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Phương thức thanh toán</th>
                                        <th>Loại giao dịch</th>
                                        <th class="text-right">Tổng tiền</th>
                                        <th class="text-right">Số giao dịch</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($summaryByPaymentMethod)): ?>
                                        <?php foreach($summaryByPaymentMethod as $item): ?>
                                            <?php 
                                                $transactionTypes = ShiftDetail::getTransactionTypes();
                                                $transactionType = isset($transactionTypes[$item['transaction_type']]) ? 
                                                    $transactionTypes[$item['transaction_type']] : 'Unknown';
                                            ?>
                                            <tr>
                                                <td><?= Html::encode($item['name']) ?></td>
                                                <td><?= Html::encode($transactionType) ?></td>
                                                <td class="text-right"><?= Yii::$app->formatter->asCurrency($item['total']) ?></td>
                                                <td class="text-right"><?= $item['count'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Không có dữ liệu</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($topProducts)): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger">
                            <h3 class="card-title">Top sản phẩm bán chạy</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Mã sản phẩm</th>
                                        <th>Tên sản phẩm</th>
                                        <th class="text-right">Số lượng</th>
                                        <th class="text-right">Doanh thu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topProducts as $index => $product): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= Html::encode($product['code']) ?></td>
                                        <td><?= Html::encode($product['name']) ?></td>
                                        <td class="text-right"><?= $product['total_qty'] ?></td>
                                        <td class="text-right"><?= Yii::$app->formatter->asCurrency($product['total_amount']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>