<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;
use common\models\StockMovement;

/* @var $this yii\web\View */
/* @var $model common\models\Product */

$dataProvider = new ActiveDataProvider([
    'query' => $model->getStockMovements(),
    'sort' => [
        'defaultOrder' => [
            'movement_date' => SORT_DESC,
        ],
    ],
    'pagination' => [
        'pageSize' => 10,
    ],
]);

// Movement type labels
$movementTypes = [
    1 => ['label' => 'Nhập kho', 'class' => 'success'],
    2 => ['label' => 'Xuất kho', 'class' => 'danger'],
    3 => ['label' => 'Chuyển kho', 'class' => 'info'],
    4 => ['label' => 'Kiểm kê', 'class' => 'warning'],
];
?>

<div class="product-stock-history mt-4">
    <div class="card">
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'attribute' => 'movement_date',
                        'format' => 'datetime',
                    ],
                    [
                        'attribute' => 'movement_type',
                        'format' => 'raw',
                        'value' => function ($model) use ($movementTypes) {
                            $type = $movementTypes[$model->movement_type] ?? ['label' => 'Không xác định', 'class' => 'secondary'];
                            return '<span class="badge badge-' . $type['class'] . '">' . $type['label'] . '</span>';
                        },
                        'filter' => array_map(function($item) { return $item['label']; }, $movementTypes),
                    ],
                    [
                        'attribute' => 'source_warehouse_id',
                        'value' => function ($model) {
                            return $model->sourceWarehouse ? $model->sourceWarehouse->name : null;
                        },
                    ],
                    [
                        'attribute' => 'destination_warehouse_id',
                        'value' => function ($model) {
                            return $model->destinationWarehouse ? $model->destinationWarehouse->name : null;
                        },
                    ],
                    [
                        'attribute' => 'quantity',
                        'value' => function ($model) {
                            $sign = $model->movement_type == 2 ? '-' : '+';
                            if ($model->movement_type == 3) {
                                $sign = '';
                            }
                            return $sign . $model->quantity . ' ' . ($model->unit ? $model->unit->abbreviation : '');
                        },
                    ],
                    [
                        'attribute' => 'balance',
                        'value' => function ($model) {
                            return $model->balance . ' ' . ($model->unit ? $model->unit->abbreviation : '');
                        },
                    ],
                    [
                        'attribute' => 'reference_type',
                        'value' => function ($model) {
                            $types = [
                                'stock_in' => 'Phiếu nhập kho',
                                'stock_out' => 'Phiếu xuất kho',
                                'stock_transfer' => 'Phiếu chuyển kho',
                                'stock_check' => 'Phiếu kiểm kê',
                                'order' => 'Đơn hàng',
                                'return' => 'Đơn trả hàng',
                            ];
                            return $types[$model->reference_type] ?? $model->reference_type;
                        },
                    ],
                    [
                        'attribute' => 'reference_id',
                        'format' => 'raw',
                        'value' => function ($model) {
                            if (!$model->reference_id) {
                                return null;
                            }
                            
                            $urls = [
                                'stock_in' => ['/stock-in/view', 'id' => $model->reference_id],
                                'stock_out' => ['/stock-out/view', 'id' => $model->reference_id],
                                'stock_transfer' => ['/stock-transfer/view', 'id' => $model->reference_id],
                                'stock_check' => ['/stock-check/view', 'id' => $model->reference_id],
                                'order' => ['/order/view', 'id' => $model->reference_id],
                                'return' => ['/return/view', 'id' => $model->reference_id],
                            ];
                            
                            if (isset($urls[$model->reference_type])) {
                                return Html::a('#' . $model->reference_id, $urls[$model->reference_type], [
                                    'target' => '_blank',
                                    'data-pjax' => 0
                                ]);
                            }
                            
                            return '#' . $model->reference_id;
                        },
                    ],
                    [
                        'attribute' => 'note',
                        'format' => 'ntext',
                    ],
                    [
                        'attribute' => 'created_by',
                        'value' => function ($model) {
                            return $model->createdBy ? $model->createdBy->username : null;
                        },
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>