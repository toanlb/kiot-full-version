<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\WarrantyStatus */

// Prepare the form action URL
$formAction = $model->isNewRecord ? ['warranty-status/create'] : ['warranty-status/update', 'id' => $model->id];
?>

<div class="warranty-status-form">
    <form id="warranty-status-form" action="<?= Url::to($formAction) ?>" method="post">
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="warrantystatus-name">Name</label>
                    <input type="text" id="warrantystatus-name" name="WarrantyStatus[name]" class="form-control" value="<?= Html::encode($model->name) ?>" required>
                    <?php if ($model->getErrors('name')): ?>
                        <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('name')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="warrantystatus-color">Color</label>
                    <div class="input-group">
                        <input type="text" id="warrantystatus-color" name="WarrantyStatus[color]" class="form-control" value="<?= Html::encode($model->color ?? '#3c8dbc') ?>">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <input type="color" id="warrantystatus-color-picker" value="<?= Html::encode($model->color ?? '#3c8dbc') ?>">
                            </span>
                        </div>
                    </div>
                    <?php if ($model->getErrors('color')): ?>
                        <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('color')) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="warrantystatus-sort-order">Sort Order</label>
                    <input type="number" id="warrantystatus-sort-order" name="WarrantyStatus[sort_order]" class="form-control" value="<?= Html::encode($model->sort_order) ?>">
                    <?php if ($model->getErrors('sort_order')): ?>
                        <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('sort_order')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Status Preview</label>
                    <div>
                        <span id="status-preview" class="badge" style="background-color: <?= $model->color ?? '#3c8dbc' ?>; padding: 8px 12px; font-size: 14px;">
                            <?= Html::encode($model->name ?? 'Status Name') ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="warrantystatus-description">Description</label>
                    <textarea id="warrantystatus-description" name="WarrantyStatus[description]" class="form-control" rows="4"><?= Html::encode($model->description) ?></textarea>
                    <?php if ($model->getErrors('description')): ?>
                        <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('description')) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-success"><?= $model->isNewRecord ? 'Create' : 'Update' ?></button>
            <a href="<?= Url::to(['index']) ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
$js = <<<JS
    // Handle color picker changes
    $(document).ready(function() {
        // Update color input when color picker changes
        $('#warrantystatus-color-picker').on('input', function() {
            var selectedColor = $(this).val();
            $('#warrantystatus-color').val(selectedColor);
            updateStatusPreview();
        });
        
        // Update color picker when color input changes
        $('#warrantystatus-color').on('input', function() {
            var colorValue = $(this).val();
            $('#warrantystatus-color-picker').val(colorValue);
            updateStatusPreview();
        });
        
        // Update preview when name changes
        $('#warrantystatus-name').on('input', function() {
            updateStatusPreview();
        });
        
        // Function to update status preview
        function updateStatusPreview() {
            var name = $('#warrantystatus-name').val() || 'Status Name';
            var color = $('#warrantystatus-color').val() || '#3c8dbc';
            
            $('#status-preview').css('background-color', color);
            $('#status-preview').text(name);
        }
    });
JS;
$this->registerJs($js);
?>