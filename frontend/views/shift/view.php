<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\DetailView;
use common\models\Shift;
use common\models\ShiftDetail;

/* @var $this yii\web\View */
/* @var $model common\models\Shift */
/* @var $detailsProvider yii\data\ActiveDataProvider */

$this->title = 'Ca làm việc #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Quản lý ca làm việc', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="shift-view">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?php if ($model->status == Shift::STATUS_OPEN): ?>
                    <?= Html::a('<i class="fas fa-lock"></i> Đóng ca', ['close', 'id' => $model->id], [
                        'class' => 'btn btn-warning btn-sm mr-2',
                    ]) ?>
                <?php endif; ?>

                <?= Html::a('<i class="fas fa-list"></i> Chi tiết giao dịch', ['detail', 'id' => $model->id], [
                    'class' => 'btn btn-info btn-sm mr-2',
                ]) ?>

                <?php if ($model->status == Shift::STATUS_CLOSED): ?>
                    <?= Html::a('<i class="fas fa-chart-bar"></i> Báo cáo', ['report', 'id' => $model->id], [
                        'class' => 'btn btn-success btn-sm',
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped table-bordered detail-view'],
                        'attributes' => [
                            'id',
                            [
                                'attribute' => 'warehouse_id',
                                'value' => $model->warehouse->name,
                            ],
                            [
                                'attribute' => 'user_id',
                                'value' => $model->user->username,
                            ],
                            [
                                'attribute' => 'cashier_id',
                                'value' => $model->cashier ? $model->cashier->username : null,
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
                                'attribute' => 'status',
                                'value' => $model->status == Shift::STATUS_OPEN ? 'Đang mở' : 'Đã đóng',
                            ],
                        ],
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped table-bordered detail-view'],
                        'attributes' => [
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
                                [
                                    'attribute' => 'difference',
                                    'format' => 'currency',
                                    'contentOptions' => ($model->difference < 0) ? ['class' => 'text-danger'] : 
                                                    (($model->difference > 0) ? ['class' => 'text-success'] : []),
                                ],
                            ],
                            'note:ntext',
                        ],
                    ]) ?>
                </div>
            </div>

            <div class="mt-4">
                <h4>Chi tiết theo phương thức thanh toán</h4>
                <?= GridView::widget([
                    'dataProvider' => $detailsProvider,
                    'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                    'summary' => '',
                    'columns' => [
                        [
                            'attribute' => 'payment_method_id',
                            'value' => function ($model) {
                                return $model->paymentMethod ? $model->paymentMethod->name : 'N/A';
                            },
                        ],
                        [
                            'attribute' => 'transaction_type',
                            'value' => function ($model) {
                                $types = ShiftDetail::getTransactionTypes();
                                return isset($types[$model->transaction_type]) ? $types[$model->transaction_type] : 'Unknown';
                            },
                        ],
                        [
                            'attribute' => 'total_amount',
                            'format' => 'currency',
                        ],
                        'transaction_count',
                        'note:ntext',
                    ],
                ]) ?>
            </div>
        </div>
    </div>
</div>