<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Phiếu kiểm kê #' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'Phiếu kiểm kê', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$js = '
// Xử lý khi nhấn nút điều chỉnh
$("#btn-adjust").on("click", function() {
    if (confirm("Bạn có chắc chắn muốn điều chỉnh tồn kho theo số liệu kiểm kê này?")) {
        var data = {};
        $(".adjustment-checkbox:checked").each(function() {
            var id = $(this).data("id");
            data[id] = {
                adjustment_approved: 1
            };
        });
        
        $.ajax({
            url: "' . \yii\helpers\Url::to(['adjust', 'id' => $model->id]) . '",
            type: "POST",
            data: {adjustments: data, _csrf: yii.getCsrfToken()},
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    if (response.completed) {
                        location.reload();
                    }
                } else {
                    alert("Lỗi: " + response.message);
                }
            },
            error: function() {
                alert("Có lỗi xảy ra khi thực hiện điều chỉnh.");
            }
        });
    }
});

// Chọn tất cả
$("#check-all").on("change", function() {
    $(".adjustment-checkbox").prop("checked", $(this).is(":checked"));
});
';

$this->registerJs($js);
?>

<div class="stock-check-view">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?php if ($model->status == \common\models\StockCheck::STATUS_DRAFT): ?>
                    <?= Html::a('<i class="fas fa-edit"></i> Cập nhật', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm mr-1']) ?>
                    <?= Html::a('<i class="fas fa-check"></i> Xác nhận', ['approve', 'id' => $model->id], [
                        'class' => 'btn btn-success btn-sm mr-1',
                        'data' => [
                            'confirm' => 'Bạn có chắc chắn muốn xác nhận phiếu kiểm kê này?',
                            'method' => 'post',
                        ],
                    ]) ?>
                <?php endif; ?>
                
                <?php if ($model->status == \common\models\StockCheck::STATUS_CONFIRMED): ?>
                    <button id="btn-adjust" class="btn btn-warning btn-sm mr-1">
                        <i class="fas fa-sync-alt"></i> Điều chỉnh tồn kho
                    </button>
                <?php endif; ?>
                
                <?php if ($model->status != \common\models\StockCheck::STATUS_ADJUSTED && $model->status != \common\models\StockCheck::STATUS_CANCELED): ?>
                    <?= Html::a('<i class="fas fa-ban"></i> Hủy', ['cancel', 'id' => $model->id], [
                        'class' => 'btn btn-danger btn-sm mr-1',
                        'data' => [
                            'confirm' => 'Bạn có chắc chắn muốn hủy phiếu kiểm kê này?',
                            'method' => 'post',
                        ],
                    ]) ?>
                <?php endif; ?>
                
                <?= Html::a('<i class="fas fa-print"></i> In', ['print', 'id' => $model->id], [
                    'class' => 'btn btn-default btn-sm',
                    'target' => '_blank',
                ]) ?>
            </div>
        </div>
        
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'code',
                            [
                                'attribute' => 'warehouse_id',
                                'value' => $model->warehouse->name,
                            ],
                            'check_date:datetime',
                            [
                                'attribute' => 'status',
                                'value' => function ($model) {
                                    return $model->getStatusLabel();
                                },
                                'format' => 'raw',
                            ],
                            'note:ntext',
                        ],
                    ]) ?>
                </div>
                
                <div class="col-md-6">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'created_at:datetime',
                            [
                                'attribute' => 'created_by',
                                'value' => $model->createdBy ? $model->createdBy->full_name : null,
                            ],
                            [
                                'attribute' => 'approved_by',
                                'value' => $model->approvedBy ? $model->approvedBy->full_name : null,
                            ],
                            'approved_at:datetime',
                            'updated_at:datetime',
                        ],
                    ]) ?>
                </div>
            </div>
            <hr>
            
            <h4>Chi tiết kiểm kê</h4>
            
            <?php if ($model->status == \common\models\StockCheck::STATUS_CONFIRMED): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Chọn các sản phẩm cần điều chỉnh tồn kho, sau đó nhấn nút "Điều chỉnh tồn kho".
            </div>
            <?php endif; ?>
            
            <?php Pjax::begin(); ?>
            <?= GridView::widget([
                'dataProvider' => new \yii\data\ArrayDataProvider([
                    'allModels' => $model->stockCheckDetails,
                    'pagination' => false,
                ]),
                'columns' => [
                    [
                        'class' => 'yii\grid\CheckboxColumn',
                        'checkboxOptions' => function ($model) {
                            return [
                                'class' => 'adjustment-checkbox',
                                'data-id' => $model->id,
                                'disabled' => $model->difference == 0 || $model->adjustment_approved == 1 || $model->stockCheck->status != \common\models\StockCheck::STATUS_CONFIRMED,
                                'checked' => $model->adjustment_approved == 1,
                            ];
                        },
                        'header' => Html::checkBox('selection_all', false, [
                            'id' => 'check-all',
                            'disabled' => $model->status != \common\models\StockCheck::STATUS_CONFIRMED,
                        ]),
                        'visible' => $model->status == \common\models\StockCheck::STATUS_CONFIRMED,
                    ],
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
                    'batch_number',
                    [
                        'attribute' => 'system_quantity',
                        'value' => function ($model) {
                            return $model->system_quantity . ' ' . $model->unit->abbreviation;
                        },
                    ],
                    [
                        'attribute' => 'actual_quantity',
                        'value' => function ($model) {
                            return $model->actual_quantity . ' ' . $model->unit->abbreviation;
                        },
                    ],
                    [
                        'attribute' => 'difference',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $diff = $model->difference;
                            $class = 'text-success';
                            $prefix = '+';
                            
                            if ($diff < 0) {
                                $class = 'text-danger';
                                $prefix = '';
                            } elseif ($diff == 0) {
                                $class = 'text-muted';
                                $prefix = '';
                            }
                            
                            return '<span class="' . $class . '">' . $prefix . $diff . ' ' . $model->unit->abbreviation . '</span>';
                        },
                    ],
                    [
                        'attribute' => 'adjustment_approved',
                        'label' => 'Đã điều chỉnh',
                        'format' => 'boolean',
                        'visible' => $model->status == \common\models\StockCheck::STATUS_CONFIRMED || $model->status == \common\models\StockCheck::STATUS_ADJUSTED,
                    ],
                    'note:ntext',
                ],
            ]); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>