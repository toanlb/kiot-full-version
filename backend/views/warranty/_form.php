<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\Warranty */
/* @var $products array */
/* @var $customers array */
/* @var $statuses array */

// Prepare the form action URL
$formAction = $model->isNewRecord ? ['warranty/create'] : ['warranty/update', 'id' => $model->id];
?>

<div class="warranty-form">
    <form id="warranty-form" action="<?= Url::to($formAction) ?>" method="post">
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="warranty-code">Warranty Code</label>
                    <input type="text" id="warranty-code" name="Warranty[code]" class="form-control" value="<?= Html::encode($model->code) ?>" <?= $model->isNewRecord ? '' : 'readonly' ?>>
                    <?php if ($model->getErrors('code')): ?>
                        <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('code')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="warranty-product-id">Product</label>
                    <select id="warranty-product-id" name="Warranty[product_id]" class="form-control select2" data-placeholder="Select product...">
                        <option value=""></option>
                        <?php foreach ($products as $id => $name): ?>
                            <option value="<?= $id ?>" <?= $model->product_id == $id ? 'selected' : '' ?>>
                                <?= Html::encode($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($model->getErrors('product_id')): ?>
                        <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('product_id')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="warranty-customer-id">Customer</label>
                    <select id="warranty-customer-id" name="Warranty[customer_id]" class="form-control select2" data-placeholder="Select customer...">
                        <option value=""></option>
                        <?php foreach ($customers as $id => $name): ?>
                            <option value="<?= $id ?>" <?= $model->customer_id == $id ? 'selected' : '' ?>>
                                <?= Html::encode($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($model->getErrors('customer_id')): ?>
                        <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('customer_id')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="warranty-serial-number">Serial Number</label>
                    <input type="text" id="warranty-serial-number" name="Warranty[serial_number]" class="form-control" value="<?= Html::encode($model->serial_number) ?>">
                    <?php if ($model->getErrors('serial_number')): ?>
                        <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('serial_number')) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="warranty-start-date">Start Date</label>
                    <div class="input-group">
                        <input type="text" id="warranty-start-date" name="Warranty[start_date]" class="form-control datepicker" value="<?= $model->start_date ? date('Y-m-d', strtotime($model->start_date)) : '' ?>">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        </div>
                    </div>
                    <?php if ($model->getErrors('start_date')): ?>
                        <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('start_date')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="warranty-end-date">End Date</label>
                    <div class="input-group">
                        <input type="text" id="warranty-end-date" name="Warranty[end_date]" class="form-control datepicker" value="<?= $model->end_date ? date('Y-m-d', strtotime($model->end_date)) : '' ?>">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        </div>
                    </div>
                    <?php if ($model->getErrors('end_date')): ?>
                        <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('end_date')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="warranty-status-id">Status</label>
                    <select id="warranty-status-id" name="Warranty[status_id]" class="form-control select2" data-placeholder="Select status...">
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

                <?php if (!$model->isNewRecord): ?>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="warranty-active" name="Warranty[active]" value="1" <?= $model->active ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="warranty-active">Active</label>
                        </div>
                        <?php if ($model->getErrors('active')): ?>
                            <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('active')) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="warranty-note">Notes</label>
                    <textarea id="warranty-note" name="Warranty[note]" class="form-control" rows="6"><?= Html::encode($model->note) ?></textarea>
                    <?php if ($model->getErrors('note')): ?>
                        <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('note')) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!$model->isNewRecord): ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="warranty-order-id">Order ID</label>
                        <input type="text" id="warranty-order-id" name="Warranty[order_id]" class="form-control" value="<?= Html::encode($model->order_id) ?>" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="warranty-order-detail-id">Order Detail ID</label>
                        <input type="text" id="warranty-order-detail-id" name="Warranty[order_detail_id]" class="form-control" value="<?= Html::encode($model->order_detail_id) ?>" readonly>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <button type="submit" class="btn btn-success"><?= $model->isNewRecord ? 'Create' : 'Update' ?></button>
            <a href="<?= Url::to(['index']) ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
$productInfoUrl = Url::to(['warranty/get-product-info']);
$customerInfoUrl = Url::to(['warranty/get-customer-info']);

$js = <<<JS
    // Initialize select2 for dropdown fields
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
        
        // Initialize datepicker
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
        
        // Handle product change
        $('#warranty-product-id').on('change', function() {
            var productId = $(this).val();
            if (productId) {
                $.ajax({
                    url: '$productInfoUrl',
                    data: {id: productId},
                    dataType: 'json',
                    success: function(data) {
                        if (data.error) {
                            console.log(data.error);
                        } else {
                            $('#warranty-start-date').val(data.start_date);
                            $('#warranty-end-date').val(data.end_date);
                        }
                    },
                    error: function(xhr) {
                        console.log('Error:', xhr);
                    }
                });
            }
        });
        
        // Handle customer change
        $('#warranty-customer-id').on('change', function() {
            var customerId = $(this).val();
            if (customerId) {
                $.ajax({
                    url: '$customerInfoUrl',
                    data: {id: customerId},
                    dataType: 'json',
                    success: function(data) {
                        if (data.error) {
                            console.log(data.error);
                        }
                    },
                    error: function(xhr) {
                        console.log('Error:', xhr);
                    }
                });
            }
        });
    });
JS;
$this->registerJs($js);
?>