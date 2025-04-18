<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use kartik\daterange\DateRangePicker;
use miloschuman\highcharts\Highcharts;

$this->title = 'Báo cáo tồn kho';
$this->params['breadcrumbs'][] = ['label' => 'Tồn kho', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$js = '
// Lọc báo cáo
$("#report-filter-form").on("submit", function(e) {
    e.preventDefault();
    var params = $(this).serialize();
    window.location.href = "' . \yii\helpers\Url::to(['report']) . '?" + params;
});

// Xuất Excel
$("#export-excel").on("click", function() {
    var params = $("#report-filter-form").serialize();
    window.location.href = "' . \yii\helpers\Url::to(['export']) . '?" + params;
});
';

$this->registerJs($js);
?>

<div class="stock-report">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <button id="export-excel" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Xuất Excel
                </button>
            </div>
        </div>
        <div class="card-body">
            <form id="report-filter-form" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <?= Select2::widget([
                            'name' => 'warehouse_id',
                            'value' => Yii::$app->request->get('warehouse_id'),
                            'data' => array_merge(['' => 'Tất cả kho hàng'], $warehouses),
                            'options' => ['placeholder' => 'Chọn kho hàng...'],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]); ?>
                    </div>
                    <div class="col-md-3">
                        <?= Select2::widget([
                            'name' => 'category_id',
                            'value' => Yii::$app->request->get('category_id'),
                            'data' => array_merge(['' => 'Tất cả danh mục'], \common\models\ProductCategory::getList()),
                            'options' => ['placeholder' => 'Chọn danh mục...'],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]); ?>
                    </div>
                    <div class="col-md-3">
                        <?= DateRangePicker::widget([
                            'name' => 'date_range',
                            'value' => Yii::$app->request->get('date_range'),
                            'convertFormat' => true,
                            'pluginOptions' => [
                                'locale' => [
                                    'format' => 'Y-m-d',
                                    'separator' => ' - ',
                                ],
                                'opens' => 'left',
                            ],
                            'options' => [
                                'placeholder' => 'Chọn khoảng thời gian...',
                                'class' => 'form-control',
                            ],
                        ]); ?>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Lọc báo cáo
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            
            <ul class="nav nav-tabs" id="reportTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="table-tab" data-toggle="tab" href="#table-content" role="tab" aria-controls="table-content" aria-selected="true">Bảng dữ liệu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="chart-tab" data-toggle="tab" href="#chart-content" role="tab" aria-controls="chart-content" aria-selected="false">Biểu đồ</a>
                </li>
            </ul>
            
            <div class="tab-content mt-3" id="reportTabContent">
                <div class="tab-pane fade show active" id="table-content" role="tabpanel" aria-labelledby="table-tab">
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
                                'value' => function ($model) {
                                    return $model->quantity . ' ' . $model->product->unit->abbreviation;
                                },
                            ],
                            [
                                'attribute' => 'product.cost_price',
                                'label' => 'Giá nhập',
                                'value' => function ($model) {
                                    return Yii::$app->formatter->asCurrency($model->product->cost_price);
                                },
                            ],
                            [
                                'label' => 'Giá trị tồn kho',
                                'value' => function ($model) {
                                    return Yii::$app->formatter->asCurrency($model->quantity * $model->product->cost_price);
                                },
                            ],
                            [
                                'attribute' => 'updated_at',
                                'format' => 'datetime',
                                'filter' => false,
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a('<i class="fas fa-eye"></i>', ['view', 'product_id' => $model->product_id, 'warehouse_id' => $model->warehouse_id], [
                                            'title' => 'Xem chi tiết',
                                            'class' => 'btn btn-primary btn-sm',
                                        ]);
                                    },
                                ],
                            ],
                        ],
                    ]); ?>
                    <?php Pjax::end(); ?>
                </div>
                
                <div class="tab-pane fade" id="chart-content" role="tabpanel" aria-labelledby="chart-tab">
                    <div class="row">
                        <div class="col-md-6">
                            <?= Highcharts::widget([
                                'options' => [
                                    'title' => ['text' => 'Giá trị tồn kho theo kho hàng'],
                                    'plotOptions' => [
                                        'pie' => [
                                            'allowPointSelect' => true,
                                            'cursor' => 'pointer',
                                            'dataLabels' => [
                                                'enabled' => true,
                                                'format' => '<b>{point.name}</b>: {point.percentage:.1f} %'
                                            ]
                                        ]
                                    ],
                                    'series' => [
                                        [
                                            'type' => 'pie',
                                            'name' => 'Giá trị tồn kho',
                                            'data' => $warehouseStockData,
                                        ]
                                    ]
                                ]
                            ]); ?>
                        </div>
                        
                        <div class="col-md-6">
                            <?= Highcharts::widget([
                                'options' => [
                                    'title' => ['text' => 'Giá trị tồn kho theo danh mục sản phẩm'],
                                    'plotOptions' => [
                                        'pie' => [
                                            'allowPointSelect' => true,
                                            'cursor' => 'pointer',
                                            'dataLabels' => [
                                                'enabled' => true,
                                                'format' => '<b>{point.name}</b>: {point.percentage:.1f} %'
                                            ]
                                        ]
                                    ],
                                    'series' => [
                                        [
                                            'type' => 'pie',
                                            'name' => 'Giá trị tồn kho',
                                            'data' => $categoryStockData,
                                        ]
                                    ]
                                ]
                            ]); ?>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <?= Highcharts::widget([
                                'options' => [
                                    'title' => ['text' => 'Top 10 sản phẩm tồn kho nhiều nhất'],
                                    'xAxis' => [
                                        'categories' => $topProductNames,
                                        'labels' => [
                                            'rotation' => -45,
                                            'style' => ['fontSize' => '12px']
                                        ]
                                    ],
                                    'yAxis' => [
                                        'title' => ['text' => 'Giá trị (VNĐ)']
                                    ],
                                    'series' => [
                                        [
                                            'type' => 'column',
                                            'name' => 'Giá trị tồn kho',
                                            'data' => $topProductValues,
                                            'color' => '#007bff'
                                        ]
                                    ]
                                ]
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>