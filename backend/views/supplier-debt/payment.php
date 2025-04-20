<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use common\models\Supplier;

/* @var $this yii\web\View */
/* @var $paymentModel common\models\Payment */
/* @var $debtModel common\models\SupplierDebt */
/* @var $paymentMethods array */

$this->title = 'Thanh toán công nợ nhà cung cấp';
$this->params['breadcrumbs'][] = ['label' => 'Quản lý công nợ nhà cung cấp', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="supplier-debt-payment card">
    <div class="card-header bg-success text-white">
        <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
    </div>
    <div class="card-body">
        <?php $form = ActiveForm::begin(); ?>
        
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($paymentModel, 'supplier_id')->widget(Select2::classname(), [
                    'data' => ArrayHelper::map(Supplier::find()->where(['>', 'debt_amount', 0])->orderBy(['name' => SORT_ASC])->all(), 'id', function($model) {
                        return $model->code . ' - ' . $model->name . ' (Công nợ: ' . Yii::$app->formatter->asCurrency($model->debt_amount) . ')';
                    }),
                    'options' => [
                        'placeholder' => 'Chọn nhà cung cấp',
                        'onchange' => 'updateDebtInfo(this.value)',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($paymentModel, 'payment_date')->widget(DatePicker::classname(), [
                    'options' => ['placeholder' => 'Chọn ngày thanh toán'],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true
                    ]
                ]) ?>
            </div>
            <div class="col-md-3">
                <div class="form-group field-current-debt">
                    <label class="control-label">Công nợ hiện tại</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">₫</span>
                        </div>
                        <input type="text" id="current-debt" class="form-control" readonly>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($paymentModel, 'payment_method_id')->dropDownList($paymentMethods, ['prompt' => 'Chọn phương thức thanh toán']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($paymentModel, 'amount')->textInput(['type' => 'number', 'step' => '0.01', 'min' => '0']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($paymentModel, 'code')->textInput(['maxlength' => true, 'readonly' => true]) ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($paymentModel, 'transaction_code')->textInput(['maxlength' => true, 'placeholder' => 'Mã giao dịch (nếu có)']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($paymentModel, 'account_number')->textInput(['maxlength' => true, 'placeholder' => 'Số tài khoản (nếu có)']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($paymentModel, 'bank_name')->textInput(['maxlength' => true, 'placeholder' => 'Tên ngân hàng (nếu có)']) ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($paymentModel, 'description')->textarea(['rows' => 3, 'placeholder' => 'Nhập mô tả về việc thanh toán']) ?>
            </div>
        </div>
        
        <div class="form-group">
            <?= Html::submitButton('<i class="fas fa-save"></i> Xác nhận thanh toán', ['class' => 'btn btn-success']) ?>
            <?= Html::a('<i class="fas fa-ban"></i> Hủy', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
        
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$script = <<<JS
function updateDebtInfo(supplierId) {
    if (supplierId) {
        $.ajax({
            url: '/supplier/get-list',
            data: {q: supplierId},
            dataType: 'json',
            success: function(data) {
                if (data.length > 0) {
                    $('#current-debt').val(formatCurrency(data[0].debt_amount));
                    $('#payment-amount').val(data[0].debt_amount);
                }
            }
        });
    } else {
        $('#current-debt').val('');
        $('#payment-amount').val('');
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', { minimumFractionDigits: 0 }).format(amount);
}

// Initialize on page load if supplier is selected
$(document).ready(function() {
    var supplierId = $('#payment-supplier_id').val();
    if (supplierId) {
        updateDebtInfo(supplierId);
    }
});
JS;
$this->registerJs($script);
?>