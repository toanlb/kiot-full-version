<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\ProductCategory;
use common\models\ProductUnit;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Quản lý Sản phẩm';
$this->params['breadcrumbs'][] = $this->title;

$priceTier = [
    "0-100000" => "< 100.000 đ",
    "100000-500000" => "100.000 đ - 500.000 đ",
    "500000-1000000" => "500.000 đ - 1.000.000 đ",
    "1000000-5000000" => "1.000.000 đ - 5.000.000 đ",
    "5000000-10000000" => "> 5.000.000 đ"
];
?>
<div class="product-index">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?= Html::a('<i class="fas fa-plus"></i> Thêm sản phẩm', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
                <?= Html::a('<i class="fas fa-file-excel"></i> Xuất Excel', ['export'], ['class' => 'btn btn-primary btn-sm']) ?>
                <?= Html::a('<i class="fas fa-file-import"></i> Nhập Excel', ['import'], ['class' => 'btn btn-warning btn-sm']) ?>
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="btn-group btn-group-sm view-type-buttons" role="group">
                        <button type="button" class="btn btn-default active" data-view="table"><i class="fas fa-list"></i> Dạng bảng</button>
                        <button type="button" class="btn btn-default" data-view="grid"><i class="fas fa-th-large"></i> Dạng lưới</button>
                    </div>
                    <div class="btn-group btn-group-sm ml-2 bulk-actions" role="group">
                        <button type="button" class="btn btn-danger" id="bulk-delete-btn" disabled><i class="fas fa-trash"></i> Xóa đã chọn</button>
                    </div>
                </div>
            </div>

            <?php Pjax::begin(['id' => 'product-grid-pjax']); ?>
            
            <div class="table-view">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        [
                            'class' => 'yii\grid\CheckboxColumn',
                            'checkboxOptions' => function ($model) {
                                return ['value' => $model->id, 'class' => 'product-checkbox'];
                            },
                        ],
                        [
                            'attribute' => 'id',
                            'headerOptions' => ['style' => 'width:60px'],
                        ],
                        [
                            'attribute' => 'image',
                            'label' => 'Hình ảnh',
                            'format' => 'html',
                            'headerOptions' => ['style' => 'width:80px'],
                            'value' => function ($model) {
                                $mainImage = $model->getMainImage()->one();
                                if ($mainImage) {
                                    
                                    return Html::img('@web/' . $mainImage->image, [
                                        'width' => '50px',
                                        'class' => 'img-thumbnail product-thumbnail'
                                    ]);
                                }
                                return Html::img('@web/img/no-image.png', [
                                    'width' => '50px',
                                    'class' => 'img-thumbnail product-thumbnail'
                                ]);
                            },
                            'filter' => false,
                        ],
                        [
                            'attribute' => 'code',
                            'headerOptions' => ['style' => 'width:120px'],
                            'format' => 'raw',
                            'value' => function ($model) {
                                return Html::a($model->code, ['view', 'id' => $model->id], ['data-pjax' => 0]);
                            }
                        ],
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'value' => function ($model) {
                                $badges = '';
                                if ($model->is_combo) {
                                    $badges .= ' <span class="badge badge-info">Combo</span>';
                                }
                                if ($model->warranty_period > 0) {
                                    $badges .= ' <span class="badge badge-secondary">BH: ' . $model->warranty_period . ' tháng</span>';
                                }
                                return $model->name . $badges;
                            }
                        ],
                        [
                            'attribute' => 'category_id',
                            'value' => 'category.name',
                            'filter' => $categories,
                        ],
                        [
                            'attribute' => 'selling_price',
                            'format' => 'currency',
                            'headerOptions' => ['class' => 'text-right', 'style' => 'width:120px'],
                            'contentOptions' => ['class' => 'text-right'],
                            'filter' => Html::activeDropDownList(
                                $searchModel,
                                'price_range',
                                $priceTier,
                                ['class' => 'form-control', 'prompt' => 'Tất cả']
                            ),
                        ],
                        [
                            'attribute' => 'status',
                            'filter' => [1 => 'Kích hoạt', 0 => 'Vô hiệu hóa'],
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width:100px'],
                            'value' => function ($model) {
                                return $model->status == 1 
                                    ? '<span class="badge badge-success">Kích hoạt</span>' 
                                    : '<span class="badge badge-danger">Vô hiệu hóa</span>';
                            },
                        ],
                        [
                            'attribute' => 'has_stock',
                            'label' => 'Tồn kho',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width:80px'],
                            'value' => function ($model) {
                                $stock = $model->getTotalStock();
                                if ($stock <= 0) {
                                    return '<span class="badge badge-danger">Hết hàng</span>';
                                } elseif ($stock <= $model->min_stock) {
                                    return '<span class="badge badge-warning">' . $stock . '</span>';
                                } else {
                                    return '<span class="badge badge-success">' . $stock . '</span>';
                                }
                            },
                            'filter' => [1 => 'Còn hàng', 0 => 'Hết hàng'],
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'headerOptions' => ['style' => 'width:100px'],
                            'template' => '{view} {update} {delete}',
                            'buttons' => [
                                'view' => function ($url, $model) {
                                    return Html::a('<i class="fas fa-eye"></i>', $url, [
                                        'title' => 'Xem',
                                        'class' => 'btn btn-sm btn-info',
                                        'data-pjax' => 0
                                    ]);
                                },
                                'update' => function ($url, $model) {
                                    return Html::a('<i class="fas fa-edit"></i>', $url, [
                                        'title' => 'Cập nhật',
                                        'class' => 'btn btn-sm btn-primary',
                                        'data-pjax' => 0
                                    ]);
                                },
                                'delete' => function ($url, $model) {
                                    return Html::a('<i class="fas fa-trash"></i>', $url, [
                                        'title' => 'Xóa',
                                        'class' => 'btn btn-sm btn-danger',
                                        'data' => [
                                            'confirm' => 'Bạn có chắc muốn xóa sản phẩm này?',
                                            'method' => 'post',
                                        ],
                                    ]);
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
            
            <div class="grid-view" style="display: none;">
                <div class="row">
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                    <div class="col-md-3 col-sm-6">
                        <div class="card product-card mb-4">
                            <div class="product-img-container">
                                <?php
                                $mainImage = $model->getMainImage()->one();
                                $mainImage = $mainImage ? $mainImage : null;
                                if ($mainImage) {
                                    echo Html::img('@web/' . $mainImage->image, [
                                        'class' => 'card-img-top product-img',
                                        'alt' => $model->name
                                    ]);
                                } else {
                                    echo Html::img('@web/img/no-image.png', [
                                        'class' => 'card-img-top product-img',
                                        'alt' => $model->name
                                    ]);
                                }
                                ?>
                                <?php if ($model->status == 0): ?>
                                <span class="status-badge badge badge-danger">Vô hiệu hóa</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-2">
                                <h5 class="card-title"><?= Html::encode($model->name) ?></h5>
                                <p class="card-text text-muted"><?= Html::encode($model->code) ?></p>
                                <p class="card-text text-primary font-weight-bold"><?= Yii::$app->formatter->asCurrency($model->selling_price) ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="product-badges">
                                        <?php if ($model->is_combo): ?>
                                            <span class="badge badge-info">Combo</span>
                                        <?php endif; ?>
                                        <?php
                                        $stock = $model->getTotalStock();
                                        if ($stock <= 0) {
                                            echo '<span class="badge badge-danger">Hết hàng</span>';
                                        } elseif ($stock <= $model->min_stock) {
                                            echo '<span class="badge badge-warning">Sắp hết</span>';
                                        }
                                        ?>
                                    </div>
                                    <div class="btn-group">
                                        <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $model->id], [
                                            'class' => 'btn btn-sm btn-info',
                                            'title' => 'Xem',
                                        ]) ?>
                                        <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $model->id], [
                                            'class' => 'btn btn-sm btn-primary',
                                            'title' => 'Cập nhật',
                                        ]) ?>
                                        <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $model->id], [
                                            'class' => 'btn btn-sm btn-danger',
                                            'title' => 'Xóa',
                                            'data' => [
                                                'confirm' => 'Bạn có chắc muốn xóa sản phẩm này?',
                                                'method' => 'post',
                                            ],
                                        ]) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?= \yii\widgets\LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'options' => ['class' => 'pagination pagination-sm justify-content-center'],
                    'linkContainerOptions' => ['class' => 'page-item'],
                    'linkOptions' => ['class' => 'page-link'],
                    'disabledListItemSubTagOptions' => ['class' => 'page-link'],
                ]); ?>
            </div>
            
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>

<?php
$bulkDeleteUrl = Url::to(['bulk-delete']);
$js = <<<JS
// View type switcher
$('.view-type-buttons button').on('click', function() {
    $('.view-type-buttons button').removeClass('active');
    $(this).addClass('active');
    
    var viewType = $(this).data('view');
    if (viewType === 'table') {
        $('.table-view').show();
        $('.grid-view').hide();
    } else {
        $('.table-view').hide();
        $('.grid-view').show();
    }
});

// Handle bulk actions
$('.product-checkbox').on('change', function() {
    var checkedCount = $('.product-checkbox:checked').length;
    $('#bulk-delete-btn').prop('disabled', checkedCount === 0);
});

$('#bulk-delete-btn').on('click', function() {
    if (confirm('Bạn có chắc muốn xóa các sản phẩm đã chọn?')) {
        var selectedIds = [];
        $('.product-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });
        
        $.ajax({
            url: '$bulkDeleteUrl',
            type: 'POST',
            data: {ids: selectedIds},
            success: function(response) {
                location.reload();
            }
        });
    }
});

// Product image hover zoom effect
$('.product-thumbnail').hover(function() {
    $(this).css('transform', 'scale(2)');
    $(this).css('z-index', '1000');
}, function() {
    $(this).css('transform', 'scale(1)');
    $(this).css('z-index', '1');
});
JS;
$this->registerJs($js);

$css = <<<CSS
.product-thumbnail {
    transition: transform 0.3s;
}
.product-img-container {
    position: relative;
    height: 150px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
}
.product-img {
    max-height: 150px;
    object-fit: contain;
}
.status-badge {
    position: absolute;
    top: 5px;
    right: 5px;
}
.product-card {
    transition: box-shadow 0.3s;
}
.product-card:hover {
    box-shadow: 0 0 10px rgba(0,0,0,0.2);
}
CSS;
$this->registerCss($css);
?>