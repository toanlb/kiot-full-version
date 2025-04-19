<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $model common\models\Product */
/* @var $form yii\widgets\ActiveForm */

$initialPreview = [];
$initialPreviewConfig = [];

if (!$model->isNewRecord) {
    $images = $model->productImages;
    foreach ($images as $image) {
        $initialPreview[] = Yii::$app->urlManager->createUrl('/' . $image->image);
        $initialPreviewConfig[] = [
            'caption' => basename($image->image),
            'key' => $image->id,
            'extra' => [
                'id' => $image->id,
                'is_main' => $image->is_main
            ]
        ];
    }
}
?>

<div class="product-form-images">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Hình ảnh sản phẩm</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Hãy tải lên hình ảnh sản phẩm. Bạn có thể tải lên nhiều hình cùng lúc.
                        Hình đầu tiên sẽ được đặt làm hình đại diện, hoặc bạn có thể chọn hình đại diện sau.
                    </div>

                    <?php if (!$model->isNewRecord): ?>
                        <div class="row mb-4" id="product-images-container">
                            <?php if (empty($images)): ?>
                                <div class="col-12 text-center">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Sản phẩm chưa có hình ảnh nào.
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($images as $index => $image): ?>
                                    <div class="col-md-3 col-sm-4 mb-3 image-item" data-id="<?= $image->id ?>">
                                        <div class="card <?= $image->is_main ? 'border-primary' : '' ?>">
                                            <div class="image-container">
                                                <?= Html::img(Yii::$app->urlManager->createUrl('/' . $image->image), [
                                                    'class' => 'card-img-top',
                                                    'alt' => $model->name
                                                ]) ?>
                                            </div>
                                            <div class="card-body p-2">
                                                <div class="btn-group btn-group-sm w-100">
                                                    <?php if (!$image->is_main): ?>
                                                        <button type="button" class="btn btn-primary set-main-image-btn" data-id="<?= $image->id ?>">
                                                            <i class="fas fa-star"></i> Đặt làm ảnh chính
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-outline-primary" disabled>
                                                            <i class="fas fa-check"></i> Ảnh chính
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-danger delete-image-btn" data-id="<?= $image->id ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?= $form->field($model, 'imageFiles[]')->widget(FileInput::classname(), [
                        'options' => [
                            'multiple' => true,
                            'accept' => 'image/*'
                        ],
                        'pluginOptions' => [
                            'initialPreview' => $initialPreview,
                            'initialPreviewAsData' => true,
                            'initialPreviewConfig' => $initialPreviewConfig,
                            'overwriteInitial' => false,
                            'maxFileSize' => 2048,
                            'showRemove' => false,
                            'showUpload' => false,
                            'browseClass' => 'btn btn-primary',
                            'browseIcon' => '<i class="fas fa-camera"></i> ',
                            'browseLabel' => 'Chọn hình ảnh',
                            'showCaption' => false,
                            'showCancel' => false,
                            'layoutTemplates' => [
                                'main1' => '{preview}<div class="input-group {class}">{browse}{caption}</div>',
                            ],
                            'fileActionSettings' => [
                                'showRemove' => true,
                                'showUpload' => false,
                                'showZoom' => true,
                                'showDrag' => false,
                            ],
                            'msgPlaceholder' => 'Chọn hình ảnh...',
                            'msgSelected' => '{n} tệp đã chọn',
                            'msgFileRequired' => 'Bạn phải chọn một tệp để tải lên.',
                            'msgSizeTooLarge' => 'Tệp "{name}" ({size} KB) vượt quá kích thước tối đa cho phép là {maxSize} KB.',
                            'msgInvalidFileExtension' => 'Không hỗ trợ định dạng "{name}". Chỉ hỗ trợ các định dạng "{extensions}".',
                        ],
                        'pluginEvents' => [
                            'fileclear' => 'function() { console.log("File clear"); }',
                        ]
                    ])->label(false); ?>

                    <?= Html::hiddenInput('main_image_id', '', ['id' => 'main-image-id']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$deleteImageUrl = Url::to(['product/delete-image']);
$js = <<<JS
// Set main image
$(document).on('click', '.set-main-image-btn', function() {
    var imageId = $(this).data('id');
    $('#main-image-id').val(imageId);
    
    // Update UI
    $('.image-item .card').removeClass('border-primary');
    $('.set-main-image-btn').removeClass('d-none');
    $('.main-image-label').addClass('d-none');
    
    $(this).closest('.card').addClass('border-primary');
    $(this).addClass('d-none');
    $(this).siblings('.main-image-label').removeClass('d-none');
});

// Delete image
$(document).on('click', '.delete-image-btn', function() {
    var imageId = $(this).data('id');
    var imageItem = $(this).closest('.image-item');
    
    if (confirm('Bạn có chắc muốn xóa hình ảnh này?')) {
        $.ajax({
            url: '$deleteImageUrl',
            type: 'POST',
            data: {id: imageId},
            success: function(response) {
                if (response.success) {
                    imageItem.fadeOut('fast', function() {
                        $(this).remove();
                        
                        // If no images left, show warning
                        if ($('.image-item').length === 0) {
                            $('#product-images-container').html(
                                '<div class="col-12 text-center">' +
                                    '<div class="alert alert-warning">' +
                                        '<i class="fas fa-exclamation-triangle"></i> Sản phẩm chưa có hình ảnh nào.' +
                                    '</div>' +
                                '</div>'
                            );
                        }
                    });
                } else {
                    alert('Không thể xóa hình ảnh: ' + response.error);
                }
            },
            error: function() {
                alert('Đã xảy ra lỗi khi xóa hình ảnh.');
            }
        });
    }
});
JS;
$this->registerJs($js);

$css = <<<CSS
.image-container {
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background-color: #f8f9fa;
}

.image-container img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
}

.card-body .btn-group {
    margin-top: 5px;
}
CSS;
$this->registerCss($css);
?>