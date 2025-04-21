<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\jui\DatePicker;

/* @var $this yii\web\View */
/* @var $suppliers common\models\Supplier[] */
/* @var $totalDebt float */

$this->title = 'Báo cáo công nợ nhà cung cấp';
$this->params['breadcrumbs'][] = ['label' => 'Công nợ nhà cung cấp', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="supplier-debt-report card">
    <div class="card-header">
        <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        <div class="card-tools">
            <?= Html::a('<i class="fas fa-file-excel"></i> Xuất Excel', ['export'], ['class' => 'btn btn-success btn-sm']) ?>
            <?= Html::a('<i class="fas fa-arrow-left"></i> Quay lại', ['index'], ['class' => 'btn btn-secondary btn-sm']) ?>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <h4><i class="icon fas fa-info"></i> Tổng công nợ</h4>
            <p>Tổng công nợ hiện tại: <strong><?= Yii::$app->formatter->asCurrency($totalDebt) ?></strong></p>
        </div>

        <div class="mb-3">
            <h5>Lọc báo cáo</h5>
            <?php $form = \yii\widgets\ActiveForm::begin([
                'method' => 'get',
                'action' => ['report'],
                'options' => ['class' => 'form-inline']
            ]); ?>
            
            <div class="input-group mr-2">
                <div class="input-group-prepend">
                    <span class="input-group-text">Từ ngày</span>
                </div>
                <?= DatePicker::widget([
                    'name' => 'from_date',
                    'value' => Yii::$app->request->get('from_date'),
                    'language' => 'vi',
                    'dateFormat' => 'yyyy-MM-dd',
                    'options' => ['class' => 'form-control', 'placeholder' => 'Từ ngày'],
                    'clientOptions' => [
                        'changeMonth' => true,
                        'changeYear' => true,
                        'showButtonPanel' => true,
                    ],
                ]) ?>
            </div>
            
            <div class="input-group mr-2">
                <div class="input-group-prepend">
                    <span class="input-group-text">Đến ngày</span>
                </div>
                <?= DatePicker::widget([
                    'name' => 'to_date',
                    'value' => Yii::$app->request->get('to_date'),
                    'language' => 'vi',
                    'dateFormat' => 'yyyy-MM-dd',
                    'options' => ['class' => 'form-control', 'placeholder' => 'Đến ngày'],
                    'clientOptions' => [
                        'changeMonth' => true,
                        'changeYear' => true,
                        'showButtonPanel' => true,
                    ],
                ]) ?>
            </div>
            
            <?= Html::submitButton('<i class="fas fa-filter"></i> Lọc', ['class' => 'btn btn-primary']) ?>
            
            <?php \yii\widgets\ActiveForm::end(); ?>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Mã nhà cung cấp</th>
                        <th>Tên nhà cung cấp</th>
                        <th class="text-right">Công nợ</th>
                        <th class="text-center">Tỷ lệ</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $i = 1;
                    foreach ($suppliers as $supplier): 
                        $percent = $totalDebt > 0 ? ($supplier->debt_amount / $totalDebt) * 100 : 0;
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= $supplier->code ?></td>
                        <td><?= $supplier->name ?></td>
                        <td class="text-right"><?= Yii::$app->formatter->asCurrency($supplier->debt_amount) ?></td>
                        <td class="text-center">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?= $percent ?>%" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"><?= number_format($percent, 1) ?>%</div>
                            </div>
                        </td>
                        <td class="text-center">
                            <?= Html::a('<i class="fas fa-eye"></i>', ['index', 'SupplierDebtSearch[supplier_id]' => $supplier->id], [
                                'class' => 'btn btn-sm btn-info',
                                'title' => 'Xem lịch sử',
                                'data-toggle' => 'tooltip',
                            ]) ?>
                            <?= Html::a('<i class="fas fa-money-bill-wave"></i>', ['payment', 'supplier_id' => $supplier->id], [
                                'class' => 'btn btn-sm btn-primary',
                                'title' => 'Thanh toán',
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
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-right">Tổng cộng:</th>
                        <th class="text-right"><?= Yii::$app->formatter->asCurrency($totalDebt) ?></th>
                        <th class="text-center">100%</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php
$script = <<< JS
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});
JS;
$this->registerJs($script);
?>