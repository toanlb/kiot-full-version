<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model model */
/* @var $form ActiveForm */
/* @var $title string */
/* @var $formContent string */
/* @var $cancelUrl string */
?>

<div class="form-view">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-edit mr-2"></i> <?= Html::encode($title) ?>
            </h3>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin(['id' => 'main-form', 'options' => ['class' => 'form']]); ?>
            
            <?= $formContent ?>
            
            <div class="form-group text-right">
                <?= Html::a('<i class="fas fa-times"></i> Hủy', $cancelUrl, ['class' => 'btn btn-default mr-2']) ?>
                <?= Html::submitButton('<i class="fas fa-save"></i> Lưu', ['class' => 'btn btn-primary']) ?>
            </div>
            
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>