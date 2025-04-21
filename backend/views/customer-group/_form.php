<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\CustomerGroup */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="customer-group-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true])->hint('Tên nhóm khách hàng phải là duy nhất.') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'discount_rate')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'max' => '100'
                ])->hint('Tỷ lệ chiết khấu mặc định cho các khách hàng thuộc nhóm này, từ 0 đến 100%.') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'description')->textarea(['rows' => 6])->hint('Mô tả chi tiết về nhóm khách hàng và các chính sách áp dụng.') ?>
            </div>
        </div>
    </div>

    <div class="card-footer">
        <?= Html::submitButton('<i class="fas fa-save"></i> Lưu', ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="fas fa-undo"></i> Quay lại', ['index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>