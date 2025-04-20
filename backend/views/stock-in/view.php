<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use common\models\StockIn;

/* @var $this yii\web\View */
/* @var $model common\models\StockIn */
/* @var $details common\models\StockInDetail[] */

$this->title = 'Phiếu nhập kho: ' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'Quản lý nhập kho', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-in-view">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?php if ($model->status == StockIn::STATUS_DRAFT): ?>
                    <?= Html::a('<i class="fas fa-edit"></i> Cập nhật', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
                <?php endif; ?>
                
                <?php if ($model->canConfirm()): ?>
                    <?= Html::a('<i class="fas fa-check"></i> Xác nhận', ['approve', 'id' => $model->id], [
                        'class' => 'btn btn-success btn-sm',
                        'data' => [
                            'confirm' => 'Bạn có chắc muốn xác nhận phiếu nhập kho này?',
                            'method' => 'post',
                        ],
                    ]) ?>
                <?php endif; ?>
                
                <?php if ($model->canComplete()): ?>
                    <?= Html::a('<i class="fas fa-check-double"></i> Hoàn thành', ['complete', 'id' => $model->id], [
                        'class' => 'btn btn-info btn-sm',
                        'data' => [
                            'confirm' => 'Bạn có chắc muốn hoàn thành phiếu nhập kho này?',
                            'method' => 'post',
                        ],
                    ]) ?>
                <?php endif; ?>
                
                <?php if ($model->canCancel()): ?>
                    <?= Html::a('<i class="fas fa-ban"></i> Hủy phiếu', ['cancel', 'id' => $model->id], [
                        'class' => 'btn btn-danger btn-sm',
                        'data' => [
                            'confirm' => 'Bạn có chắc muốn hủy phiếu nhập kho này?',
                            'method' => 'post',
                        ],
                    ]) ?>
                <?php endif; ?>
                
                <?= Html::a('<i class="fas fa-print"></i> In phiếu', ['print', 'id' => $model->id], ['class' => 'btn btn-default btn-sm', 'target' => '_blank']) ?>
                <?= Html::a('<i class="fas fa-arrow-left"></i> Quay lại', ['index'], ['class' => 'btn btn-default btn-sm']) ?>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Thông tin phiếu nhập</h5>
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'code',
                            [
                                'attribute' => 'warehouse_id',
                                'value' => $model->warehouse->name,
                            ],
                            [
                                'attribute' => 'supplier_id',
                                'value' => $model->supplier ? $model->supplier->name : '',
                            ],
                            [
                                'attribute' => 'stock_in_date',
                                'format' => ['date', 'php:d/m/Y H:i'],
                            ],
                            'reference_number',
                            [
                                'attribute' => 'status',
                                'value' => $model->getStatusLabel(),
                                'format' => 'raw',
                                'contentOptions' => function($model) {
                                    $class = '';
                                    switch ($model->status) {
                                        case StockIn::STATUS_DRAFT: $class = 'text-secondary'; break;
                                        case StockIn::STATUS_CONFIRMED: $class = 'text-primary'; break;
                                        case StockIn::STATUS_COMPLETED: $class = 'text-success'; break;
                                        case StockIn::STATUS_CANCELED: $class = 'text-danger'; break;
                                    }
                                    return ['class' => $class];
                                }
                            ],
                            'note:ntext',
                            [
                                'attribute' => 'created_at',
                                'format' => ['date', 'php:d/m/Y H:i'],
                            ],
                            [
                                'attribute' => 'created_by',
                                'value' => $model->createdBy->username,
                            ],
                            [
                                'attribute' => 'approved_by',
                                'value' => $model->approvedBy ? $model->approvedBy->username : '',
                            ],
                            [
                                'attribute' => 'approved_at',
                                'format' => ['date', 'php:d/m/Y H:i'],
                                'visible' => !empty($model->approved_at),
                            ],
                        ],
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <h5>Thông tin thanh toán</h5>
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'total_amount',
                                'format' => ['decimal', 0],
                            ],
                            [
                                'attribute' => 'discount_amount',
                                'format' => ['decimal', 0],
                            ],
                            [
                                'attribute' => 'tax_amount',
                                'format' => ['decimal', 0],
                            ],
                            [
                                'attribute' => 'final_amount',
                                'format' => ['decimal', 0],
                                'contentOptions' => ['class' => 'text-bold'],
                            ],
                            [
                                'attribute' => 'paid_amount',
                                'format' => ['decimal', 0],
                            ],
                            [
                                'label' => 'Còn lại',
                                'value' => Yii::$app->formatter->asDecimal($model->getRemainingAmount(), 0),
                                'contentOptions' => ['class' => 'text-danger text-bold'],
                            ],
                            [
                                'attribute' => 'payment_status',
                                'value' => $model->getPaymentStatusLabel(),
                                'contentOptions' => function($model) {
                                    $class = '';
                                    switch ($model->payment_status) {
                                        case StockIn::PAYMENT_STATUS_UNPAID: $class = 'text-danger'; break;
                                        case StockIn::PAYMENT_STATUS_PARTIALLY_PAID: $class = 'text-warning'; break;
                                        case StockIn::PAYMENT_STATUS_PAID: $class = 'text-success'; break;
                                    }
                                    return ['class' => $class];
                                }
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
            
            <h5 class="mt-4">Chi tiết phiếu nhập</h5>
            <div class="table-responsive">
                <?= GridView::widget([
                    'dataProvider' => new \yii\data\ArrayDataProvider([
                        'allModels' => $details,
                    ]),
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'product_id',
                            'value' => function ($model) {
                                return $model->product->code . ' - ' . $model->product->name;
                            },
                        ],
                        'batch_number',
                        [
                            'attribute' => 'expiry_date',
                            'format' => ['date', 'php:d/m/Y'],
                        ],
                        [
                            'attribute' => 'quantity',
                            'headerOptions' => ['class' => 'text-right'],
                            'contentOptions' => ['class' => 'text-right'],
                        ],
                        [
                            'attribute' => 'unit_id',
                            'value' => function ($model) {
                                return $model->unit->name;
                            },
                        ],
                        [
                            'attribute' => 'unit_price',
                            'format' => ['decimal', 0],
                            'headerOptions' => ['class' => 'text-right'],
                            'contentOptions' => ['class' => 'text-right'],
                        ],
                        [
                            'attribute' => 'discount_amount',
                            'format' => ['decimal', 0],
                            'headerOptions' => ['class' => 'text-right'],
                            'contentOptions' => ['class' => 'text-right'],
                        ],
                        [
                            'attribute' => 'tax_amount',
                            'format' => ['decimal', 0],
                            'headerOptions' => ['class' => 'text-right'],
                            'contentOptions' => ['class' => 'text-right'],
                        ],
                        [
                            'attribute' => 'total_price',
                            'format' => ['decimal', 0],
                            'headerOptions' => ['class' => 'text-right'],
                            'contentOptions' => ['class' => 'text-right font-weight-bold'],
                        ],
                        'note',
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>