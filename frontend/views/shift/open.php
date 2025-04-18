<?php
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Shift */

$this->title = 'Mở ca làm việc';
$this->params['breadcrumbs'][] = ['label' => 'Quản lý ca làm việc', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="shift-open">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-play mr-2"></i> <?= Html::encode($this->title) ?>
            </h3>
        </div>
        <div class="card-body">
            <?php 
            $form = ActiveForm::begin(['id' => 'shift-form']);
            
            // Chuẩn bị nội dung form
            $formContent = '
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Kho hàng</label>
                        <input type="text" class="form-control" value="' . Html::encode($model->warehouse->name) . '" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Nhân viên</label>
                        <input type="text" class="form-control" value="' . Html::encode(Yii::$app->user->identity->username) . '" readonly>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    ' . $form->field($model, 'opening_amount')->textInput(['type' => 'number', 'step' => '1000']) . '
                </div>
                <div class="col-md-6">
                    ' . $form->field($model, 'note')->textarea(['rows' => 4]) . '
                </div>
            </div>
            ';
            
            // Sử dụng $formContent
            echo $formContent;
            ?>
            
            <div class="form-group text-right">
                <?= Html::a('<i class="fas fa-times"></i> Hủy', ['index'], ['class' => 'btn btn-default mr-2']) ?>
                <?= Html::submitButton('<i class="fas fa-play"></i> Mở ca', ['class' => 'btn btn-success']) ?>
            </div>
            
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>