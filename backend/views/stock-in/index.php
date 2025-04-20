<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\StockIn;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\StockInSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Quản lý nhập kho';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-in-index">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?= Html::a('<i class="fas fa-plus"></i> Tạo phiếu nhập kho', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
            </div>
        </div>
        <div class="card-body">
            <?php Pjax::begin(); ?>
            
            <div class="table-responsive">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'code',
                        [
                            'attribute' => 'warehouse_id',
                            'value' => 'warehouse.name',
                            'filter' => \common\models\Warehouse::getList(),
                        ],
                        [
                            'attribute' => 'supplier_id',
                            'value' => 'supplier.name',
                            'filter' => \common\models\Supplier::getList(),
                        ],
                        [
                            'attribute' => 'stock_in_date',
                            'format' => ['date', 'php:d/m/Y H:i'],
                            'filter' => \yii\jui\DatePicker::widget([
                                'model' => $searchModel,
                                'attribute' => 'stock_in_date',
                                'language' => 'vi',
                                'dateFormat' => 'dd/MM/yyyy',
                                'options' => ['class' => 'form-control'],
                            ]),
                        ],
                        [
                            'attribute' => 'final_amount',
                            'format' => ['decimal', 0],
                            'headerOptions' => ['class' => 'text-right'],
                            'contentOptions' => ['class' => 'text-right'],
                        ],
                        [
                            'attribute' => 'status',
                            'value' => function($model) {
                                return $model->getStatusLabel();
                            },
                            'filter' => StockIn::getStatusList(),
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
                        [
                            'attribute' => 'payment_status',
                            'value' => function($model) {
                                return $model->getPaymentStatusLabel();
                            },
                            'filter' => StockIn::getPaymentStatusList(),
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
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view} {update} {print}',
                            'buttons' => [
                                'update' => function($url, $model, $key) {
                                    return $model->status == StockIn::STATUS_DRAFT ? 
                                        Html::a('<i class="fas fa-edit"></i>', $url, ['title' => 'Cập nhật']) : '';
                                },
                                'print' => function($url, $model, $key) {
                                    return Html::a('<i class="fas fa-print"></i>', $url, [
                                        'title' => 'In phiếu',
                                        'target' => '_blank'
                                    ]);
                                }
                            ],
                        ],
                    ],
                ]); ?>
            </div>
            
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>