<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;
use yii\helpers\ArrayHelper;
use common\models\Supplier;
use common\models\SupplierDebt;

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
        <div class="col-md-3">
            <?= $form->field($model, 'supplier_id')->dropDownList(
                ArrayHelper::map(Supplier::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name'),
                [
                    'prompt' => 'Tất cả nhà cung cấp',
                    'class' => 'form-control'
                ]
            ) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'type')->dropDownList(
                ['' => 'Tất cả loại'] + SupplierDebt::getTypes()
            ) ?>
        </div>
        <div class="col-md-3">
            <label>Số tiền</label>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'amount_from')->textInput(['placeholder' => 'Từ'])->label(false) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'amount_to')->textInput(['placeholder' => 'Đến'])->label(false) ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <label>Ngày giao dịch</label>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'transaction_date_from')->widget(DatePicker::className(), [
                        'language' => 'vi',
                        'dateFormat' => 'yyyy-MM-dd',
                        'options' => [
                            'class' => 'form-control',
                            'placeholder' => 'Từ ngày'
                        ],
                        'clientOptions' => [
                            'changeMonth' => true,
                            'changeYear' => true,
                            'showButtonPanel' => true,
                        ],
                    ])->label(false) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'transaction_date_to')->widget(DatePicker::className(), [
                        'language' => 'vi',
                        'dateFormat' => 'yyyy-MM-dd',
                        'options' => [
                            'class' => 'form-control',
                            'placeholder' => 'Đến ngày'
                        ],
                        'clientOptions' => [
                            'changeMonth' => true,
                            'changeYear' => true,
                            'showButtonPanel' => true,
                        ],
                    ])->label(false) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-3">
            <?= $form->field($model, 'description')->textInput(['placeholder' => 'Mô tả']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'reference_type')->dropDownList([
                '' => 'Tất cả',
                'stock_in' => 'Nhập kho',
                'payment' => 'Phiếu chi', 
                'receipt' => 'Phiếu thu'
            ]) ?>
        </div>
        <div class="col-md-6">
            <div class="form-group mt-4">
                <?= Html::submitButton('<i class="fas fa-search"></i> Tìm kiếm', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('<i class="fas fa-redo"></i> Đặt lại', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>