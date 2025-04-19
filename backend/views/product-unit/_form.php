<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ProductUnit */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="product-unit-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Thông tin đơn vị tính</h3>
        </div>
        <div class="card-body">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Nhập tên đơn vị tính']) ?>

            <?= $form->field($model, 'abbreviation')->textInput(['maxlength' => true, 'placeholder' => 'Nhập viết tắt của đơn vị (vd: kg, cái...)']) ?>

            <?= $form->field($model, 'is_default')->checkbox([
                'label' => 'Đặt làm đơn vị tính mặc định',
                'labelOptions' => ['class' => 'custom-control-label'],
                'uncheck' => '0',
                'value' => '1',
                'template' => '<div class="custom-control custom-switch">{input}{label}</div>',
                'options' => ['class' => 'custom-control-input']
            ]) ?>

            <div class="form-group text-center mt-3">
                <?= Html::submitButton('<i class="fas fa-save"></i> Lưu đơn vị tính', ['class' => 'btn btn-success btn-lg']) ?>
                <?= Html::a('<i class="fas fa-times"></i> Hủy', ['index'], ['class' => 'btn btn-secondary btn-lg']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>