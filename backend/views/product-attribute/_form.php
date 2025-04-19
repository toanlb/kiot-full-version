<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ProductAttribute */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="product-attribute-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Thông tin thuộc tính</h3>
        </div>
        <div class="card-body">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Nhập tên thuộc tính']) ?>

            <?= $form->field($model, 'sort_order')->textInput(['type' => 'number', 'min' => 0, 'placeholder' => 'Thứ tự hiển thị']) ?>

            <?= $form->field($model, 'is_filterable')->checkbox([
                'label' => 'Có thể lọc',
                'labelOptions' => ['class' => 'custom-control-label'],
                'uncheck' => '0',
                'value' => '1',
                'template' => '<div class="custom-control custom-switch">{input}{label}</div>',
                'options' => ['class' => 'custom-control-input']
            ]) ?>

            <div class="form-group text-center mt-3">
                <?= Html::submitButton('<i class="fas fa-save"></i> Lưu thuộc tính', ['class' => 'btn btn-success btn-lg']) ?>
                <?= Html::a('<i class="fas fa-times"></i> Hủy', ['index'], ['class' => 'btn btn-secondary btn-lg']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$js = <<<JS
// Add any JS code here if needed
JS;
$this->registerJs($js);
?>