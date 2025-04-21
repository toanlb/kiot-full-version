<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;
use yii\helpers\ArrayHelper;
use common\models\Supplier;

/* @var $this yii\web\View */
/* @var $paymentModel common\models\Payment */
/* @var $debtModel common\models\SupplierDebt */
/* @var $paymentMethods array */

$this->title = 'Thanh toán công nợ nhà cung cấp';
$this->params['breadcrumbs'][] = ['label' => 'Công nợ nhà cung cấp', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="supplier-debt-payment">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin(); ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($paymentModel, 'supplier_id')->dropDownList(
                        ArrayHelper::map(Supplier::find()->where(['>', 'debt_amount', 0])->orderBy(['name' => SORT_ASC])->all(), 'id', 'name'),
                        [
                            'prompt' => 'Chọn nhà cung cấp',
                            'class' => 'form-control',
                            'id' => 'supplier-id'
                        ]
                    ) ?>

                    <div id="debt-info" class="alert alert-info mb-3" style="display: none;"></div>

                    <?= $form->field($paymentModel, 'amount')->textInput(['type' => 'number', 'step' => '0.01']) ?>

                    <?= $form->field($paymentModel, 'payment_method_id')->dropDownList(
                        $paymentMethods,
                        ['prompt' => 'Chọn phương thức thanh toán']
                    ) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($paymentModel, 'payment_date')->widget(DatePicker::className(), [
                        'language' => 'vi',
                        'dateFormat' => 'yyyy-MM-dd',
                        'options' => [
                            'class' => 'form-control',
                            'placeholder' => 'Chọn ngày thanh toán'
                        ],
                        'clientOptions' => [
                            'changeMonth' => true,
                            'changeYear' => true,
                            'showButtonPanel' => true,
                        ],
                    ]) ?>

                    <?= $form->field($paymentModel, 'code')->textInput(['readonly' => true]) ?>

                    <?= $form->field($paymentModel, 'description')->textarea(['rows' => 3]) ?>
                </div>
            </div>

            <div class="form-group">
                <?= Html::submitButton('<i class="fas fa-save"></i> Thanh toán', ['class' => 'btn btn-success']) ?>
                <?= Html::a('<i class="fas fa-arrow-left"></i> Quay lại', ['index'], ['class' => 'btn btn-secondary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>

<?php
$script = <<< JS
// Hiển thị thông tin công nợ khi chọn nhà cung cấp
$('#supplier-id').change(function() {
    var supplierId = $(this).val();
    if (supplierId) {
        $.get('/admin/supplier/get-debt-info', {id: supplierId}, function(data) {
            $('#debt-info').html('Công nợ hiện tại: ' + data.debt_amount.toLocaleString('vi-VN') + ' VND').show();
        });
    } else {
        $('#debt-info').hide();
    }
});

// Kiểm tra số tiền thanh toán không lớn hơn công nợ
$('form').on('beforeSubmit', function() {
    var supplierId = $('#supplier-id').val();
    var amount = parseFloat($('#payment-amount').val());
    
    if (!supplierId) {
        alert('Vui lòng chọn nhà cung cấp');
        return false;
    }
    
    if (isNaN(amount) || amount <= 0) {
        alert('Vui lòng nhập số tiền thanh toán hợp lệ');
        return false;
    }
    
    return true;
});
JS;
$this->registerJs($script);
?>