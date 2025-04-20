<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use common\models\Supplier;

/* @var $this yii\web\View */
/* @var $model common\models\SupplierDebtSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="supplier-debt-search mb-4">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1,
            'class' => 'form-horizontal',
        ],
    ]); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'supplier_id')->widget(Select2::classname(), [
                'data' => ArrayHelper::map(Supplier::find()->orderBy(['name' => SORT_ASC])->all(), 'id', function($model) {
                    return $model->code . ' - ' . $model->name;
                }),
                'options' => ['placeholder' => 'Chọn nhà cung cấp'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'type')->dropDownList([
                '' => 'Tất cả loại',
                '1' => 'Nợ',
                '2' => 'Thanh toán',
            ]) ?>
        </div>
        <div class="col-md-3">
            <label>Khoảng tiền</label>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'amount_from')->textInput(['placeholder' => 'Từ'])->label(false) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'amount_to')->textInput(['placeholder' => 'Đến'])->label(false) ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'description')->textInput(['placeholder' => 'Tìm theo mô tả']) ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <label>Thời gian giao dịch</label>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'transaction_date_from')->widget(DatePicker::classname(), [
                        'options' => ['placeholder' => 'Từ ngày'],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd'
                        ]
                    ])->label(false) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'transaction_date_to')->widget(DatePicker::classname(), [
                        'options' => ['placeholder' => 'Đến ngày'],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd'
                        ]
                    ])->label(false) ?>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'reference_type')->dropDownList([
                '' => 'Tất cả',
                'stock_in' => 'Nhập kho',
                'payment' => 'Phiếu chi',
                'manual' => 'Nhập thủ công',
            ]) ?>
        </div>
        <div class="col-md-6">
            <div class="form-group mt-4">
                <?= Html::submitButton('<i class="fas fa-search"></i> Tìm kiếm', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('<i class="fas fa-redo"></i> Đặt lại', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                <?= Html::a('<i class="fas fa-file-excel"></i> Xuất kết quả này', ['export'] + Yii::$app->request->queryParams, ['class' => 'btn btn-success']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>