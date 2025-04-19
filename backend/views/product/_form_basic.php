<?php
use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\number\NumberControl;
use yii\bootstrap4\Accordion;

/* @var $this yii\web\View */
/* @var $model common\models\Product */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="product-form-basic">
    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Thông tin chính</h3>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Nhập tên sản phẩm']) ?>

                    <?= $form->field($model, 'code')->textInput([
                        'maxlength' => true, 
                        'placeholder' => 'Nhập mã sản phẩm',
                        'readonly' => !$model->isNewRecord
                    ]) ?>

                    <?= $form->field($model, 'barcode')->textInput(['maxlength' => true, 'placeholder' => 'Nhập mã vạch (nếu có)']) ?>

                    <?= $form->field($model, 'slug')->textInput(['maxlength' => true, 'placeholder' => 'Tự động tạo từ tên sản phẩm']) ?>

                    <?= $form->field($model, 'category_id')->widget(Select2::classname(), [
                        'data' => $categories,
                        'options' => ['placeholder' => 'Chọn danh mục'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]) ?>

                    <?= $form->field($model, 'unit_id')->widget(Select2::classname(), [
                        'data' => $units,
                        'options' => ['placeholder' => 'Chọn đơn vị tính'],
                        'pluginOptions' => [
                            'allowClear' => false
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">Giá & Tồn kho</h3>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'cost_price')->widget(NumberControl::classname(), [
                        'maskedInputOptions' => [
                            'prefix' => '',
                            'suffix' => ' đ',
                            'allowMinus' => false,
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'digits' => 0
                        ],
                        'displayOptions' => ['class' => 'form-control'],
                        'saveInputContainer' => ['class' => 'kv-saved-cont']
                    ]) ?>

                    <?= $form->field($model, 'selling_price')->widget(NumberControl::classname(), [
                        'maskedInputOptions' => [
                            'prefix' => '',
                            'suffix' => ' đ',
                            'allowMinus' => false,
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'digits' => 0
                        ],
                        'displayOptions' => ['class' => 'form-control'],
                        'saveInputContainer' => ['class' => 'kv-saved-cont']
                    ]) ?>

                    <?= $form->field($model, 'min_stock')->textInput(['type' => 'number', 'min' => '0', 'placeholder' => 'Nhập số lượng tồn kho tối thiểu']) ?>

                    <?= $form->field($model, 'status')->checkbox([
                        'label' => 'Kích hoạt',
                        'labelOptions' => ['class' => 'custom-control-label'],
                        'uncheck' => '0',
                        'value' => '1',
                        'template' => '<div class="custom-control custom-switch">{input}{label}</div>',
                        'options' => ['class' => 'custom-control-input']
                    ]) ?>

                    <?= $form->field($model, 'is_combo')->checkbox([
                        'label' => 'Là sản phẩm combo/bộ',
                        'labelOptions' => ['class' => 'custom-control-label'],
                        'uncheck' => '0',
                        'value' => '1',
                        'template' => '<div class="custom-control custom-switch">{input}{label}</div>',
                        'options' => ['class' => 'custom-control-input']
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Mô tả sản phẩm</h3>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'short_description')->textarea(['rows' => 3, 'placeholder' => 'Nhập mô tả ngắn gọn về sản phẩm']) ?>

                    <?= $form->field($model, 'description')->widget(\dosamigos\ckeditor\CKEditor::className(), [
                        'options' => ['rows' => 8],
                        'preset' => 'basic',
                        'clientOptions' => [
                            'height' => 300,
                            'toolbar' => [
                                ['name' => 'clipboard', 'items' => ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']],
                                ['name' => 'basicstyles', 'items' => ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']],
                                ['name' => 'paragraph', 'items' => ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']],
                                ['name' => 'links', 'items' => ['Link', 'Unlink', 'Anchor']],
                                ['name' => 'insert', 'items' => ['Image', 'Table', 'HorizontalRule']],
                                ['name' => 'styles', 'items' => ['Format', 'Font', 'FontSize']],
                                ['name' => 'colors', 'items' => ['TextColor', 'BGColor']],
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>