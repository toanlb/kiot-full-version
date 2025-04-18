<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model common\models\Warehouse */
/* @var $form yii\widgets\ActiveForm */

$managerList = ArrayHelper::map(User::find()->all(), 'id', 'full_name');
?>

<div class="warehouse-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'Ví dụ: WH001']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Ví dụ: Kho chính']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'address')->textarea(['rows' => 2, 'placeholder' => 'Nhập địa chỉ kho hàng']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'phone')->textInput(['maxlength' => true, 'placeholder' => 'Số điện thoại liên hệ kho']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'manager_id')->dropDownList($managerList, ['prompt' => '-- Chọn người quản lý --']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'is_default')->checkbox() ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'is_active')->checkbox() ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'description')->textarea(['rows' => 4, 'placeholder' => 'Mô tả chi tiết về kho hàng (không bắt buộc)']) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-save"></i> Lưu thông tin', ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="fas fa-times"></i> Hủy', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>