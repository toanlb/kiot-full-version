<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\WarrantyDetail */
/* @var $warranties array */
/* @var $statuses array */
/* @var $replacementProducts array */

// Prepare the form action URL
$formAction = $model->isNewRecord ? ['warranty-detail/create'] : ['warranty-detail/update', 'id' => $model->id];
$warranty_id = Yii::$app->request->get('warranty_id');
?>

<div class="warranty-detail-form">
    <form id="warranty-detail-form" action="<?= Url::to($formAction) ?>" method="post">
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
        
        <?php if ($warranty_id): ?>
            <input type="hidden" name="WarrantyDetail[warranty_id]" value="<?= $warranty_id ?>">
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <?php if (!$warranty_id): ?>
                <div class="form-group">
                    <label for="warrantydetail-warranty-id">Warranty</label>
                    <select id="warrantydetail-warranty-id" name="WarrantyDetail[warranty_id]" class="form-control select2" data-placeholder="Select warranty..." required>
                        <option value=""></option>
                        <?php foreach ($warranties as $id => $code): ?>
                            <option value="<?= $id ?>" <?= $model->warranty_id == $id ? 'selected' : '' ?>>
                                <?= Html::encode($code) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($model->getErrors('warranty_id')): ?>
                        <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('warranty_id')) ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="warrantydetail-service-date">Service Date</label>
                    <div class="input-group">
                        <input type="text" id="warrantydetail-service-date" name="WarrantyDetail[service_date]" class="form-control datetimepicker" value="<?= $model->service_date ? date('Y-m-d H:i:s', strtotime($model->service_date)) : date('Y-m-d H:i:s') ?>" required>
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        </div>
                    </div>
                    <?php if ($model->getErrors('service_date')): ?>
                        <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('service_date')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="warrantydetail-status-id">Status</label>
                    <select id="warrantydetail-status-id" name="WarrantyDetail[status_id]" class="form-control select2" data-placeholder="Select status..." required>
                        <option value=""></option>
                        <?php foreach ($statuses as $id => $name): ?>
                            <option value="<?= $id ?>" <?= $model->status_id == $id ? 'selected' : '' ?>>
                                <?= Html::encode($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($model->getErrors('status_id')): ?>
                        <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('status_id')) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="warrantydetail-description">Description</label>
                    <textarea id="warrantydetail-description" name="WarrantyDetail[description]" class="form-control" rows="3"><?= Html::encode($model->description) ?></textarea>
                    <?php if ($model->getErrors('description')): ?>
                        <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('description')) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="warrantydetail-diagnosis">Diagnosis</label>
                    <textarea id="warrantydetail-diagnosis" name="WarrantyDetail[diagnosis]" class="form-control" rows="3"><?= Html::encode($model->diagnosis) ?></textarea>
                    <?php if ($model->getErrors('diagnosis')): ?>
                        <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('diagnosis')) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="warrantydetail-solution">Solution</label>
                    <textarea id="warrantydetail-solution" name="WarrantyDetail[solution]" class="form-control" rows="3"><?= Html::encode($model->solution) ?></textarea>
                    <?php if ($model->getErrors('solution')): ?>
                        <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('solution')) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="warrantydetail-replacement-product-id">Replacement Product</label>
                    <select id="warrantydetail-replacement-product-id" name="WarrantyDetail[replacement_product_id]" class="form-control select2" data-placeholder="Select product (if replacing)">
                        <option value=""></option>
                        <?php foreach ($replacementProducts as $id => $name): ?>
                            <option value="<?= $id ?>" <?= $model->replacement_product_id == $id ? 'selected' : '' ?>>
                                <?= Html::encode($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($model->getErrors('replacement_product_id')): ?>
                        <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('replacement_product_id')) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="warrantydetail-replacement-cost">Replacement Cost</label>
                            <input type="number" id="warrantydetail-replacement-cost" name="WarrantyDetail[replacement_cost]" class="form-control cost-input" step="0.01" value="<?= $model->replacement_cost ?>">
                            <?php if ($model->getErrors('replacement_cost')): ?>
                                <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('replacement_cost')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="warrantydetail-service-cost">Service Cost</label>
                            <input type="number" id="warrantydetail-service-cost" name="WarrantyDetail[service_cost]" class="form-control cost-input" step="0.01" value="<?= $model->service_cost ?>">
                            <?php if ($model->getErrors('service_cost')): ?>
                                <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('service_cost')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="warrantydetail-total-cost">Total Cost</label>
                            <input type="number" id="warrantydetail-total-cost" name="WarrantyDetail[total_cost]" class="form-control" step="0.01" value="<?= $model->total_cost ?>">
                            <?php if ($model->getErrors('total_cost')): ?>
                                <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('total_cost')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="warrantydetail-is-charged" name="WarrantyDetail[is_charged]" value="1" <?= $model->is_charged ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="warrantydetail-is-charged">Charge Customer</label>
                    </div>
                    <small class="form-text text-muted">Check this if the customer will be charged for this service</small>
                    <?php if ($model->getErrors('is_charged')): ?>
                        <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('is_charged')) ?></div>
                    <?php endif; ?>
                </div>
                
                <?php if ($model->isNewRecord): ?>
                <input type="hidden" name="WarrantyDetail[handled_by]" value="<?= Yii::$app->user->id ?>">
                <?php else: ?>
                <div class="form-group">
                    <label for="warrantydetail-handled-by">Technician</label>
                    <select id="warrantydetail-handled-by" name="WarrantyDetail[handled_by]" class="form-control select2" data-placeholder="Select technician...">
                        <option value=""></option>
                        <?php 
                        $technicians = \common\models\User::find()->all();
                        foreach ($technicians as $technician): 
                        ?>
                            <option value="<?= $technician->id ?>" <?= $model->handled_by == $technician->id ? 'selected' : '' ?>>
                                <?= Html::encode($technician->full_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($model->getErrors('handled_by')): ?>
                        <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('handled_by')) ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-success"><?= $model->isNewRecord ? 'Create' : 'Update' ?></button>
            <?php if ($warranty_id): ?>
                <a href="<?= Url::to(['/warranty/view', 'id' => $warranty_id]) ?>" class="btn btn-secondary">Cancel</a>
            <?php else: ?>
                <a href="<?= Url::to(['index']) ?>" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php
// Current user ID for JavaScript use
$currentUserId = Yii::$app->user->id;
$js = <<<JS
// Initialize select2 for dropdown fields
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
    
    // Initialize datetime picker
    $('.datetimepicker').datetimepicker({
        format: 'YYYY-MM-DD HH:mm:ss',
        icons: {
            time: 'fas fa-clock',
            date: 'fas fa-calendar',
            up: 'fas fa-arrow-up',
            down: 'fas fa-arrow-down',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right',
            today: 'fas fa-calendar-check-o',
            clear: 'fas fa-trash',
            close: 'fas fa-times'
        }
    });
    
    // Calculate total cost automatically
    $('.cost-input').on('input', function() {
        var replacementCost = parseFloat($('#warrantydetail-replacement-cost').val()) || 0;
        var serviceCost = parseFloat($('#warrantydetail-service-cost').val()) || 0;
        $('#warrantydetail-total-cost').val((replacementCost + serviceCost).toFixed(2));
    });
    
    // Set handled_by to current user if not set
    if (!$('#warrantydetail-handled-by').val()) {
        $('#warrantydetail-handled-by').val({$currentUserId}).trigger('change');
    }
});
JS;

$this->registerJs($js);
?>