<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;

/* @var $this yii\web\View */
/* @var $model backend\models\CustomerGroupSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="customer-group-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <div class="card card-default collapsed-card">
        <div class="card-header">
            <h3 class="card-title">Tìm kiếm nâng cao</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body" style="display: none;">
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'name') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'discount_rate')->input('number', ['step' => '0.01', 'min' => '0', 'max' => '100']) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'created_at')->widget(DatePicker::className(), [
                        'dateFormat' => 'dd-MM-yyyy',
                        'options' => ['class' => 'form-control'],
                    ]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'description') ?>
                </div>
            </div>
        </div>
        <div class="card-footer" style="display: none;">
            <div class="form-group">
                <?= Html::submitButton('<i class="fas fa-search"></i> Tìm kiếm', ['class' => 'btn btn-primary']) ?>
                <?= Html::resetButton('<i class="fas fa-redo"></i> Đặt lại', ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>