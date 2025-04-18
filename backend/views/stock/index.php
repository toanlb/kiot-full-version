<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Tồn kho';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-index">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?= Html::a('<i class="fas fa-exclamation-triangle"></i> Hàng sắp hết', ['low-stock'], ['class' => 'btn btn-warning btn-sm mr-2']) ?>
                <?= Html::a('<i class="fas fa-file-excel"></i> Xuất Excel', ['export'], ['class' => 'btn btn-success btn-sm']) ?>
            </div>
        </div>
        <div class="card-body">
            <?php Pjax::begin(); ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'product.code',
                        'label' => 'Mã sản phẩm',
                        'value' => function ($model) {
                            return $model->product->code;
                        },
                    ],
                    [
                        'attribute' => 'product.name',
                        'label' => 'Tên sản phẩm',
                        'value' => function ($model) {
                            return $model->product->name;
                        },
                    ],
                    [
                        'attribute' => 'warehouse_id',
                        'value' => function ($model) {
                            return $model->warehouse->name;
                        },
                        'filter' => $warehouses,
                    ],
                    [
                        'attribute' => 'quantity',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $color = ($model->quantity <= $model->min_stock || $model->quantity <= $model->product->min_stock) ? 'text-danger' : '';
                            return '<span class="' . $color . '">' . $model->quantity . ' ' . $model->product->unit->abbreviation . '</span>';
                        },
                    ],
                    [
                        'attribute' => 'min_stock',
                        'value' => function ($model) {
                            return $model->min_stock ?: $model->product->min_stock;
                        },
                    ],
                    [
                        'attribute' => 'updated_at',
                        'format' => 'datetime',
                        'filter' => false,
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view} {update}',
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-eye"></i>', $url, [
                                    'title' => 'Xem chi tiết',
                                    'class' => 'btn btn-primary btn-sm',
                                ]);
                            },
                            'update' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-edit"></i>', $url, [
                                    'title' => 'Cập nhật',
                                    'class' => 'btn btn-info btn-sm',
                                ]);
                            },
                        ],
                        'urlCreator' => function ($action, $model, $key, $index) {
                            if ($action === 'view') {
                                return ['view', 'product_id' => $model->product_id, 'warehouse_id' => $model->warehouse_id];
                            }
                            if ($action === 'update') {
                                return ['update', 'product_id' => $model->product_id, 'warehouse_id' => $model->warehouse_id];
                            }
                        }
                    ],
                ],
            ]); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>