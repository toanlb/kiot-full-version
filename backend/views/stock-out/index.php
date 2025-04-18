<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Quản lý xuất kho';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-out-index">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?= Html::a('<i class="fas fa-plus"></i> Tạo phiếu xuất kho', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
            </div>
        </div>
        <div class="card-body">
            <?php Pjax::begin(); ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'code',
                    [
                        'attribute' => 'warehouse_id',
                        'value' => function ($model) {
                            return $model->warehouse->name;
                        },
                        'filter' => $warehouses,
                    ],
                    'recipient',
                    [
                        'attribute' => 'reference_type',
                        'value' => function ($model) {
                            return $model->getReferenceTypeLabel();
                        },
                        'filter' => [
                            'order' => 'Đơn hàng',
                            'return' => 'Trả nhà cung cấp',
                            'damage' => 'Hàng hỏng/lỗi',
                            'other' => 'Khác',
                        ],
                    ],
                    [
                        'attribute' => 'stock_out_date',
                        'format' => 'datetime',
                        'filter' => \kartik\daterange\DateRangePicker::widget([
                            'model' => $searchModel,
                            'attribute' => 'date_range',
                            'convertFormat' => true,
                            'pluginOptions' => [
                                'locale' => [
                                    'format' => 'Y-m-d',
                                    'separator' => ' - ',
                                ],
                            ],
                        ]),
                    ],
                    'total_amount:currency',
                    [
                        'attribute' => 'status',
                        'value' => function ($model) {
                            return $model->getStatusLabel();
                        },
                        'filter' => [
                            0 => 'Nháp',
                            1 => 'Đã xác nhận',
                            2 => 'Đã hoàn thành',
                            3 => 'Đã hủy',
                        ],
                        'format' => 'raw',
                        'contentOptions' => function ($model) {
                            return ['class' => $model->getStatusCssClass()];
                        },
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view} {update} {delete} {approve} {complete} {cancel} {print}',
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-eye"></i>', $url, [
                                    'title' => 'Xem chi tiết',
                                    'class' => 'btn btn-primary btn-sm',
                                ]);
                            },
                            'update' => function ($url, $model, $key) {
                                if ($model->status == 0) {
                                    return Html::a('<i class="fas fa-edit"></i>', $url, [
                                        'title' => 'Cập nhật',
                                        'class' => 'btn btn-info btn-sm',
                                    ]);
                                }
                                return '';
                            },
                            'delete' => function ($url, $model, $key) {
                                if ($model->status == 0) {
                                    return Html::a('<i class="fas fa-trash"></i>', $url, [
                                        'title' => 'Xóa',
                                        'class' => 'btn btn-danger btn-sm',
                                        'data' => [
                                            'confirm' => 'Bạn có chắc chắn muốn xóa phiếu xuất kho này?',
                                            'method' => 'post',
                                        ],
                                    ]);
                                }
                                return '';
                            },
                            'approve' => function ($url, $model, $key) {
                                if ($model->status == 0) {
                                    return Html::a('<i class="fas fa-check"></i>', ['approve', 'id' => $model->id], [
                                        'title' => 'Xác nhận',
                                        'class' => 'btn btn-success btn-sm',
                                        'data' => [
                                            'confirm' => 'Bạn có chắc chắn muốn xác nhận phiếu xuất kho này?',
                                            'method' => 'post',
                                        ],
                                    ]);
                                }
                                return '';
                            },
                            'complete' => function ($url, $model, $key) {
                                if ($model->status == 1) {
                                    return Html::a('<i class="fas fa-check-double"></i>', ['complete', 'id' => $model->id], [
                                        'title' => 'Hoàn thành',
                                        'class' => 'btn btn-success btn-sm',
                                        'data' => [
                                            'confirm' => 'Bạn có chắc chắn muốn hoàn thành phiếu xuất kho này? Hành động này sẽ giảm số lượng tồn kho tương ứng.',
                                            'method' => 'post',
                                        ],
                                    ]);
                                }
                                return '';
                            },
                            'cancel' => function ($url, $model, $key) {
                                if ($model->status < 2) {
                                    return Html::a('<i class="fas fa-ban"></i>', ['cancel', 'id' => $model->id], [
                                        'title' => 'Hủy',
                                        'class' => 'btn btn-warning btn-sm',
                                        'data' => [
                                            'confirm' => 'Bạn có chắc chắn muốn hủy phiếu xuất kho này?',
                                            'method' => 'post',
                                        ],
                                    ]);
                                }
                                return '';
                            },
                            'print' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-print"></i>', ['print', 'id' => $model->id], [
                                    'title' => 'In phiếu',
                                    'class' => 'btn btn-default btn-sm',
                                    'target' => '_blank',
                                ]);
                            },
                        ],
                    ],
                ],
            ]); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>