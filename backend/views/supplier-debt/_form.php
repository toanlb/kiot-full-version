<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;
use yii\helpers\ArrayHelper;
use common\models\Supplier;
use common\models\SupplierDebt;

/* @var $this yii\web\View */
/* @var $model common\models\SupplierDebt */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="supplier-debt-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Thông tin công nợ</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'supplier_id')->dropDownList(
                        ArrayHelper::map(Supplier::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name'),
                        [
                            'prompt' => 'Chọn nhà cung cấp',
                            'class' => 'form-control',
                            'id' => 'supplier-id'
                        ]
                    ) ?>

                    <?= $form->field($model, 'type')->dropDownList(
                        SupplierDebt::getTypes(),
                        [
                            'prompt' => 'Chọn loại công nợ',
                            'class' => 'form-control'
                        ]
                    ) ?>

                    <?= $form->field($model, 'amount')->textInput(['type' => 'number', 'step' => '0.01']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'transaction_date')->widget(DatePicker::className(), [
                        'language' => 'vi',
                        'dateFormat' => 'yyyy-MM-dd',
                        'options' => [
                            'class' => 'form-control',
                            'placeholder' => 'Chọn ngày giao dịch'
                        ],
                        'clientOptions' => [
                            'changeMonth' => true,
                            'changeYear' => true,
                            'showButtonPanel' => true,
                        ],
                    ]) ?>

                    <?= $form->field($model, 'description')->textarea(['rows' => 4]) ?>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="form-group">
                <?= Html::submitButton('<i class="fas fa-save"></i> Lưu', ['class' => 'btn btn-success']) ?>
                <?= Html::a('<i class="fas fa-arrow-left"></i> Quay lại', ['index'], ['class' => 'btn btn-secondary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$script = <<< JS
// Hiển thị thông tin công nợ khi chọn nhà cung cấp
$('#supplier-id').change(function() {
    var supplierId = $(this).val();
    if (supplierId) {
        $.get('/admin/supplier/get-debt-info', {id: supplierId}, function(data) {
            $('#debt-info').html('Công nợ hiện tại: ' + data.debt_amount.toLocaleString('vi-VN') + ' VND');
        });
    } else {
        $('#debt-info').html('');
    }
});
JS;
$this->registerJs($script);
?>