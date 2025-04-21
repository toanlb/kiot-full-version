<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Province;
use common\models\District;
use common\models\Ward;

/* @var $this yii\web\View */
/* @var $model common\models\Supplier */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="supplier-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Thông tin nhà cung cấp</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'readonly' => !$model->isNewRecord]) ?>

                    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'address')->textarea(['rows' => 3]) ?>

                    <?= $form->field($model, 'tax_code')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'contact_person')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'contact_phone')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'website')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'payment_term')->textInput(['type' => 'number']) ?>

                    <?= $form->field($model, 'credit_limit')->textInput(['type' => 'number', 'step' => '0.01']) ?>

                    <?= $form->field($model, 'status')->dropDownList([
                        1 => 'Hoạt động',
                        0 => 'Không hoạt động',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'province_id')->dropDownList(
                        ArrayHelper::map(Province::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name'),
                        [
                            'prompt' => 'Chọn tỉnh/thành phố',
                            'class' => 'form-control',
                            'id' => 'province-id'
                        ]
                    ) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'district_id')->dropDownList(
                        $model->province_id ? 
                            ArrayHelper::map(District::find()->where(['province_id' => $model->province_id])->orderBy(['name' => SORT_ASC])->all(), 'id', 'name') : 
                            [],
                        [
                            'prompt' => 'Chọn quận/huyện',
                            'class' => 'form-control',
                            'id' => 'district-id'
                        ]
                    ) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'ward_id')->dropDownList(
                        $model->district_id ? 
                            ArrayHelper::map(Ward::find()->where(['district_id' => $model->district_id])->orderBy(['name' => SORT_ASC])->all(), 'id', 'name') : 
                            [],
                        [
                            'prompt' => 'Chọn phường/xã',
                            'class' => 'form-control',
                            'id' => 'ward-id'
                        ]
                    ) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'bank_name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'bank_account')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'bank_account_name')->textInput(['maxlength' => true]) ?>
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
// Xử lý cascading dropdown cho tỉnh/huyện/xã
$('#province-id').change(function() {
    var provinceId = $(this).val();
    if (provinceId) {
        $.get('/admin/district/get-list', {province_id: provinceId}, function(data) {
            $('#district-id').html('<option value="">Chọn quận/huyện</option>');
            $('#ward-id').html('<option value="">Chọn phường/xã</option>');
            $.each(data, function(index, item) {
                $('#district-id').append($('<option>', {
                    value: item.id,
                    text: item.name
                }));
            });
        });
    } else {
        $('#district-id').html('<option value="">Chọn quận/huyện</option>');
        $('#ward-id').html('<option value="">Chọn phường/xã</option>');
    }
});

$('#district-id').change(function() {
    var districtId = $(this).val();
    if (districtId) {
        $.get('/admin/ward/get-list', {district_id: districtId}, function(data) {
            $('#ward-id').html('<option value="">Chọn phường/xã</option>');
            $.each(data, function(index, item) {
                $('#ward-id').append($('<option>', {
                    value: item.id,
                    text: item.name
                }));
            });
        });
    } else {
        $('#ward-id').html('<option value="">Chọn phường/xã</option>');
    }
});
JS;
$this->registerJs($script);
?>