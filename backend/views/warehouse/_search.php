<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model backend\models\search\WarehouseSearch */
/* @var $form yii\widgets\ActiveForm */

$managerList = ArrayHelper::map(User::find()->all(), 'id', 'full_name');
?>

<div class="warehouse-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-search"></i> Tìm kiếm nâng cao
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'code') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'name') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'phone') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'manager_id')->dropDownList($managerList, ['prompt' => '-- Tất cả --']) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'is_default')->dropDownList(['' => '-- Tất cả --', '1' => 'Có', '0' => 'Không']) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'is_active')->dropDownList(['' => '-- Tất cả --', '1' => 'Kích hoạt', '0' => 'Tạm khóa']) ?>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="form-group">
                <?= Html::submitButton('<i class="fas fa-search"></i> Tìm kiếm', ['class' => 'btn btn-primary']) ?>
                <?= Html::resetButton('<i class="fas fa-times"></i> Đặt lại', ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>