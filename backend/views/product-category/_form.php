<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\file\FileInput;
use dosamigos\ckeditor\CKEditor;

/* @var $this yii\web\View */
/* @var $model common\models\ProductCategory */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="product-category-form">
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Thông tin danh mục</h3>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Nhập tên danh mục']) ?>

                    <?= $form->field($model, 'slug')->textInput(['maxlength' => true, 'placeholder' => 'Tự động tạo từ tên danh mục']) ?>

                    <?= $form->field($model, 'parent_id')->widget(Select2::classname(), [
                        'data' => $categories,
                        'options' => ['placeholder' => 'Chọn danh mục cha (nếu có)'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]); ?>

                    <?= $form->field($model, 'sort_order')->textInput(['type' => 'number', 'min' => 0]) ?>

                    <?= $form->field($model, 'status')->checkbox([
                        'label' => 'Kích hoạt',
                        'labelOptions' => ['class' => 'custom-control-label'],
                        'uncheck' => '0',
                        'value' => '1',
                        'template' => '<div class="custom-control custom-switch">{input}{label}</div>',
                        'options' => ['class' => 'custom-control-input']
                    ]) ?>

                    <?= $form->field($model, 'description')->widget(CKEditor::className(), [
                        'options' => ['rows' => 6],
                        'preset' => 'basic',
                        'clientOptions' => [
                            'height' => 200,
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
        <div class="col-md-4">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">Hình ảnh</h3>
                </div>
                <div class="card-body">
                    <?php if (!$model->isNewRecord && $model->image): ?>
                        <div class="mb-3 text-center">
                            <div class="mb-2">Hình ảnh hiện tại</div>
                            <?= Html::img('@web/' . $model->image, ['class' => 'img-thumbnail', 'style' => 'max-height: 150px;']) ?>
                        </div>
                    <?php endif; ?>

                    <?= $form->field($model, 'imageFile')->widget(FileInput::classname(), [
                        'options' => [
                            'accept' => 'image/*'
                        ],
                        'pluginOptions' => [
                            'showCaption' => false,
                            'showUpload' => false,
                            'browseClass' => 'btn btn-primary btn-block',
                            'browseIcon' => '<i class="fas fa-camera"></i> ',
                            'browseLabel' => 'Chọn hình ảnh',
                            'previewFileType' => 'image',
                            'maxFileSize' => 2048,
                        ]
                    ])->label(false) ?>

                    <?php if (!$model->isNewRecord && $model->image): ?>
                        <div class="form-text text-muted">
                            Để trống nếu không muốn thay đổi hình ảnh hiện tại.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group text-center mt-3">
        <?= Html::submitButton('<i class="fas fa-save"></i> Lưu danh mục', ['class' => 'btn btn-success btn-lg']) ?>
        <?= Html::a('<i class="fas fa-times"></i> Hủy', ['index'], ['class' => 'btn btn-secondary btn-lg']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$js = <<<JS
// Generate slug from category name
$('#productcategory-name').on('input', function() {
    if (!$('#productcategory-slug').val()) {
        let slug = $(this).val().toLowerCase()
            .replace(/[áàảãạăắằẳẵặâấầẩẫậ]/g, 'a')
            .replace(/[éèẻẽẹêếềểễệ]/g, 'e')
            .replace(/[íìỉĩị]/g, 'i')
            .replace(/[óòỏõọôốồổỗộơớờởỡợ]/g, 'o')
            .replace(/[úùủũụưứừửữự]/g, 'u')
            .replace(/[ýỳỷỹỵ]/g, 'y')
            .replace(/đ/g, 'd')
            .replace(/[^a-z0-9-]/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        $('#productcategory-slug').val(slug);
    }
});
JS;
$this->registerJs($js);
?>