<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\CustomerGroup;
use common\models\Province;
use common\models\District;
use common\models\Ward;
use common\models\Customer;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\Customer */
/* @var $form yii\widgets\ActiveForm */

// Định dạng lại ngày sinh nếu có
$birthdayValue = '';
if ($model->birthday) {
    $birthdayDate = new DateTime($model->birthday);
    $birthdayValue = $birthdayDate->format('Y-m-d');
}
?>

<div class="customer-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Thông tin cơ bản</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'readonly' => !$model->isNewRecord]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'customer_group_id')->dropDownList(
                                    ArrayHelper::map(CustomerGroup::find()->orderBy('name')->all(), 'id', 'name'),
                                    ['prompt' => 'Chọn nhóm khách hàng']
                                ) ?>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'birthday')->textInput([
                                    'type' => 'date',
                                    'value' => $birthdayValue
                                ])->hint('Nhập ngày sinh của khách hàng') ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'gender')->dropDownList(Customer::getGenders(), ['prompt' => 'Chọn giới tính']) ?>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'tax_code')->textInput(['maxlength' => true]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'status')->dropDownList(Customer::getStatuses()) ?>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <?= $form->field($model, 'company_name')->textInput(['maxlength' => true]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Thông tin địa chỉ & Tài chính</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <?= $form->field($model, 'province_id')->dropDownList(
                                    ArrayHelper::map(Province::find()->orderBy('name')->all(), 'id', 'name'),
                                    [
                                        'prompt' => 'Chọn tỉnh/thành',
                                        'onchange'=>'
                                            $.post("'.Url::to(['customer/get-districts']).'", { province_id: $(this).val() })
                                                .done(function(data) {
                                                    $("#customer-district_id").html(data.output);
                                                    $("#customer-district_id").val(data.selected);
                                                    $("#customer-ward_id").html("<option value=\'\'>Chọn phường/xã</option>");
                                                });
                                        '
                                    ]
                                ); ?>
                            </div>
                            <div class="col-md-4">
                                <?= $form->field($model, 'district_id')->dropDownList(
                                    $model->province_id ? 
                                        ArrayHelper::map(District::find()->where(['province_id' => $model->province_id])->orderBy('name')->all(), 'id', 'name') : 
                                        [],
                                    [
                                        'prompt' => 'Chọn quận/huyện',
                                        'onchange'=>'
                                            $.post("'.Url::to(['customer/get-wards']).'", { district_id: $(this).val() })
                                                .done(function(data) {
                                                    $("#customer-ward_id").html(data.output);
                                                    $("#customer-ward_id").val(data.selected);
                                                });
                                        '
                                    ]
                                ); ?>
                            </div>
                            <div class="col-md-4">
                                <?= $form->field($model, 'ward_id')->dropDownList(
                                    $model->district_id ? 
                                        ArrayHelper::map(Ward::find()->where(['district_id' => $model->district_id])->orderBy('name')->all(), 'id', 'name') : 
                                        [],
                                    ['prompt' => 'Chọn phường/xã']
                                ); ?>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'credit_limit')->textInput([
                                    'type' => 'number',
                                    'step' => '1000',
                                    'min' => '0'
                                ])->hint('Hạn mức tín dụng cho phép khách hàng nợ. Để 0 nếu không cho phép nợ.') ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'debt_amount')->textInput([
                                    'type' => 'number',
                                    'step' => '1000',
                                    'min' => '0',
                                    'readonly' => !$model->isNewRecord
                                ])->hint('Số tiền nợ hiện tại.') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer">
        <?= Html::submitButton('<i class="fas fa-save"></i> Lưu', ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="fas fa-undo"></i> Quay lại', ['index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>