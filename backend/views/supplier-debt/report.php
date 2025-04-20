<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use yii\bootstrap4\Progress;

/* @var $this yii\web\View */
/* @var $suppliers common\models\Supplier[] */
/* @var $totalDebt float */

$this->title = 'Báo cáo công nợ nhà cung cấp';
$this->params['breadcrumbs'][] = ['label' => 'Quản lý công nợ nhà cung cấp', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="supplier-debt-report">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-file-excel"></i> Xuất Excel', ['export'], ['class' => 'btn btn-success btn-sm']) ?>
                        <?= Html::a('<i class="fas fa-arrow-left"></i> Quay lại', ['index'], ['class' => 'btn btn-secondary btn-sm']) ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-hand-holding-usd"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tổng công nợ</span>
                                    <span class="info-box-number"><?= Yii::$app->formatter->asCurrency($totalDebt) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Nhà cung cấp có công nợ</span>
                                    <span class="info-box-number"><?= count($suppliers) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th class="text-center">STT</th>
                                            <th>Mã NCC</th>
                                            <th>Tên nhà cung cấp</th>
                                            <th class="text-right">Công nợ</th>
                                            <th class="text-center">% Tỷ trọng</th>
                                            <th class="text-center">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($suppliers as $i => $supplier): ?>
                                            <tr>
                                                <td class="text-center"><?= $i + 1 ?></td>
                                                <td><?= $supplier->code ?></td>
                                                <td><?= $supplier->name ?></td>
                                                <td class="text-right"><?= Yii::$app->formatter->asCurrency($supplier->debt_amount) ?></td>
                                                <td class="text-center">
                                                    <?php
                                                    $percentage = ($totalDebt > 0) ? ($supplier->debt_amount / $totalDebt * 100) : 0;
                                                    echo number_format($percentage, 2) . '%';
                                                    
                                                    // Progress bar
                                                    $progressClass = 'bg-success';
                                                    if ($percentage > 30) $progressClass = 'bg-warning';
                                                    if ($percentage > 60) $progressClass = 'bg-danger';
                                                    
                                                    echo Progress::widget([
                                                        'percent' => min($percentage, 100),
                                                        'barOptions' => ['class' => $progressClass],
                                                        'options' => ['class' => 'progress-sm']
                                                    ]);
                                                    ?>
                                                </td>
                                                <td class="text-center">
                                                    <?= Html::a('<i class="fas fa-eye"></i>', ['/supplier/view', 'id' => $supplier->id], [
                                                        'class' => 'btn btn-sm btn-primary',
                                                        'title' => 'Xem nhà cung cấp',
                                                        'data-toggle' => 'tooltip',
                                                    ]) ?>
                                                    <?= Html::a('<i class="fas fa-money-bill-wave"></i>', ['payment', 'supplier_id' => $supplier->id], [
                                                        'class' => 'btn btn-sm btn-success',
                                                        'title' => 'Thanh toán công nợ',
                                                        'data-toggle' => 'tooltip',
                                                    ]) ?>
                                                    <?= Html::a('<i class="fas fa-history"></i>', ['index', 'SupplierDebtSearch[supplier_id]' => $supplier->id], [
                                                        'class' => 'btn btn-sm btn-info',
                                                        'title' => 'Lịch sử công nợ',
                                                        'data-toggle' => 'tooltip',
                                                    ]) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        
                                        <?php if (empty($suppliers)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">Không có dữ liệu công nợ</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($suppliers)): ?>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-info">
                                    <h3 class="card-title">Biểu đồ phân tích công nợ</h3>
                                </div>
                                <div class="card-body">
                                    <div id="debt-chart" style="min-height: 350px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php
                    // Chuẩn bị dữ liệu cho biểu đồ
                    $chartData = [];
                    foreach ($suppliers as $supplier) {
                        $chartData[] = [
                            'name' => $supplier->name,
                            'y' => floatval($supplier->debt_amount)
                        ];
                    }
                    
                    $chartJson = json_encode($chartData);
                    
                    $script = <<<JS
                    $(function () {
                        Highcharts.chart('debt-chart', {
                            chart: {
                                plotBackgroundColor: null,
                                plotBorderWidth: null,
                                plotShadow: false,
                                type: 'pie'
                            },
                            title: {
                                text: 'Tỷ trọng công nợ theo nhà cung cấp'
                            },
                            tooltip: {
                                pointFormat: '{series.name}: <b>{point.percentage:.2f}%</b>'
                            },
                            accessibility: {
                                point: {
                                    valueSuffix: '%'
                                }
                            },
                            plotOptions: {
                                pie: {
                                    allowPointSelect: true,
                                    cursor: 'pointer',
                                    dataLabels: {
                                        enabled: true,
                                        format: '<b>{point.name}</b>: {point.percentage:.2f} %'
                                    }
                                }
                            },
                            series: [{
                                name: 'Tỷ trọng',
                                colorByPoint: true,
                                data: $chartJson
                            }]
                        });
                    });
                    JS;
                    
                    // Đăng ký script cho biểu đồ nếu có Highcharts
                    $this->registerJsFile('https://code.highcharts.com/highcharts.js', ['depends' => [\yii\web\JqueryAsset::class]]);
                    $this->registerJs($script);
                    ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title">Lọc báo cáo công nợ</h3>
                </div>
                <div class="card-body">
                    <?php $form = \yii\widgets\ActiveForm::begin(['action' => ['report'], 'method' => 'get']); ?>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Thời gian</label>
                                <div class="input-group">
                                    <?= DatePicker::widget([
                                        'name' => 'from_date',
                                        'type' => DatePicker::TYPE_RANGE,
                                        'name2' => 'to_date',
                                        'value' => Yii::$app->request->get('from_date'),
                                        'value2' => Yii::$app->request->get('to_date'),
                                        'options' => ['placeholder' => 'Từ ngày'],
                                        'options2' => ['placeholder' => 'Đến ngày'],
                                        'pluginOptions' => [
                                            'autoclose' => true,
                                            'format' => 'yyyy-mm-dd'
                                        ]
                                    ]); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Loại giao dịch</label>
                                <?= Html::dropDownList('type', Yii::$app->request->get('type'), [
                                    '' => 'Tất cả loại',
                                    '1' => 'Nợ',
                                    '2' => 'Thanh toán',
                                ], ['class' => 'form-control']) ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Nhà cung cấp</label>
                                <?= Select2::widget([
                                    'name' => 'supplier_id',
                                    'value' => Yii::$app->request->get('supplier_id'),
                                    'data' => ArrayHelper::map($suppliers, 'id', function($model) {
                                        return $model->code . ' - ' . $model->name;
                                    }),
                                    'options' => ['placeholder' => 'Chọn nhà cung cấp'],
                                    'pluginOptions' => [
                                        'allowClear' => true
                                    ],
                                ]); ?>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group" style="margin-top: 30px;">
                                <?= Html::submitButton('<i class="fas fa-search"></i> Lọc', ['class' => 'btn btn-primary']) ?>
                                <?= Html::a('<i class="fas fa-redo"></i> Đặt lại', ['report'], ['class' => 'btn btn-outline-secondary']) ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php \yii\widgets\ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>