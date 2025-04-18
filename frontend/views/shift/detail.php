<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\DetailView;
use common\models\Shift;
use common\models\ShiftDetail;

/* @var $this yii\web\View */
/* @var $model common\models\Shift */
/* @var $ordersProvider yii\data\ActiveDataProvider */
/* @var $summaryByPaymentMethod array */

$this->title = 'Chi tiết ca làm việc #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Quản lý ca làm việc', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Ca làm việc #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Chi tiết';
?>
<div class="shift-detail">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?= Html::a('<i class="fas fa-arrow-left"></i> Quay lại', ['view', 'id' => $model->id], [
                    'class' => 'btn btn-default btn-sm',
                ]) ?>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <!-- Tabs -->
                    <ul class="nav nav-tabs" id="shift-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="orders-tab" data-toggle="tab" href="#orders" role="tab" aria-controls="orders" aria-selected="true">
                                <i class="fas fa-shopping-cart"></i> Đơn hàng
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="summary-tab" data-toggle="tab" href="#summary" role="tab" aria-controls="summary" aria-selected="false">
                                <i class="fas fa-chart-pie"></i> Thống kê theo phương thức
                            </a>
                        </li>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content mt-3" id="shift-tabs-content">
                        <!-- Orders Tab -->
                        <div class="tab-pane fade show active" id="orders" role="tabpanel" aria-labelledby="orders-tab">
                            <?= GridView::widget([
                                'dataProvider' => $ordersProvider,
                                'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                                'summary' => 'Hiển thị <b>{begin}-{end}</b> trong tổng số <b>{totalCount}</b> đơn hàng',
                                'columns' => [
                                    ['class' => 'yii\grid\SerialColumn'],
                                    'code',
                                    [
                                        'attribute' => 'customer_id',
                                        'value' => function ($model) {
                                            return $model->customer ? $model->customer->name : 'Khách lẻ';
                                        },
                                    ],
                                    [
                                        'attribute' => 'created_at',
                                        'format' => 'datetime',
                                    ],
                                    [
                                        'attribute' => 'total_amount',
                                        'format' => 'currency',
                                    ],
                                    [
                                        'attribute' => 'payment_status',
                                        'format' => 'raw',
                                        'value' => function ($model) {
                                            $statuses = [
                                                0 => '<span class="badge badge-danger">Chưa thanh toán</span>',
                                                1 => '<span class="badge badge-warning">Một phần</span>',
                                                2 => '<span class="badge badge-success">Đã thanh toán</span>',
                                            ];
                                            return $statuses[$model->payment_status] ?? 'Unknown';
                                        },
                                    ],
                                    [
                                        'attribute' => 'status',
                                        'format' => 'raw',
                                        'value' => function ($model) {
                                            $statuses = [
                                                0 => '<span class="badge badge-secondary">Nháp</span>',
                                                1 => '<span class="badge badge-info">Xác nhận</span>',
                                                2 => '<span class="badge badge-primary">Đã thanh toán</span>',
                                                3 => '<span class="badge badge-warning">Đã giao</span>',
                                                4 => '<span class="badge badge-success">Hoàn thành</span>',
                                                5 => '<span class="badge badge-danger">Hủy</span>',
                                            ];
                                            return $statuses[$model->status] ?? 'Unknown';
                                        },
                                    ],
                                    [
                                        'class' => 'yii\grid\ActionColumn',
                                        'template' => '{view}',
                                        'buttons' => [
                                            'view' => function ($url, $model) {
                                                return Html::a('<i class="fas fa-eye"></i>', ['/order/view', 'id' => $model->id], [
                                                    'title' => 'Xem chi tiết đơn hàng',
                                                    'class' => 'btn btn-primary btn-sm',
                                                    'data-toggle' => 'tooltip',
                                                ]);
                                            },
                                        ],
                                    ],
                                ],
                            ]); ?>
                        </div>
                        
                        <!-- Summary Tab -->
                        <div class="tab-pane fade" id="summary" role="tabpanel" aria-labelledby="summary-tab">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Phương thức thanh toán</th>
                                            <th>Doanh thu</th>
                                            <th>Trả hàng</th>
                                            <th>Thu</th>
                                            <th>Chi</th>
                                            <th>Tổng cộng</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $totalByType = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
                                        $grandTotal = 0;
                                        
                                        foreach ($summaryByPaymentMethod as $method): 
                                            $rowTotal = 0;
                                        ?>
                                            <tr>
                                                <td><?= Html::encode($method['payment_method']) ?></td>
                                                
                                                <?php for ($type = 1; $type <= 4; $type++): 
                                                    $amount = $method['amounts'][$type] ?? 0;
                                                    $totalByType[$type] += $amount;
                                                    $rowTotal += ($type == 2 || $type == 4) ? -$amount : $amount; // Trừ với loại 2 (trả hàng) và 4 (chi)
                                                    
                                                    if ($type == 2 || $type == 4) {
                                                        $displayAmount = $amount > 0 ? '-' . Yii::$app->formatter->asCurrency($amount) : Yii::$app->formatter->asCurrency(0);
                                                    } else {
                                                        $displayAmount = Yii::$app->formatter->asCurrency($amount);
                                                    }
                                                ?>
                                                    <td><?= $displayAmount ?></td>
                                                <?php endfor; 
                                                
                                                $grandTotal += $rowTotal;
                                                $rowTotalClass = $rowTotal < 0 ? 'text-danger' : ($rowTotal > 0 ? 'text-success' : '');
                                                ?>
                                                
                                                <td class="<?= $rowTotalClass ?>"><?= Yii::$app->formatter->asCurrency($rowTotal) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        
                                        <!-- Thêm hàng tổng cộng -->
                                        <tr class="font-weight-bold">
                                            <td>Tổng cộng</td>
                                            
                                            <?php for ($type = 1; $type <= 4; $type++): 
                                                if ($type == 2 || $type == 4) {
                                                    $displayAmount = $totalByType[$type] > 0 ? '-' . Yii::$app->formatter->asCurrency($totalByType[$type]) : Yii::$app->formatter->asCurrency(0);
                                                } else {
                                                    $displayAmount = Yii::$app->formatter->asCurrency($totalByType[$type]);
                                                }
                                            ?>
                                                <td><?= $displayAmount ?></td>
                                            <?php endfor; 
                                            
                                            $grandTotalClass = $grandTotal < 0 ? 'text-danger' : ($grandTotal > 0 ? 'text-success' : '');
                                            ?>
                                            
                                            <td class="<?= $grandTotalClass ?>"><?= Yii::$app->formatter->asCurrency($grandTotal) ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Thêm JavaScript để khởi tạo tooltip
$this->registerJs("
    $(function () {
        $('[data-toggle=\"tooltip\"]').tooltip();
    });
");
?>